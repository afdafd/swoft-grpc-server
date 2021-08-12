<?php declare(strict_types=1);

namespace Hzwz\Grpc\Server\Middleware;


use Hzwz\Grpc\Server\Parser;
use Google\Protobuf\Internal\Message;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\False_;
use Psr\Http\Message\ResponseInterface;
use ReflectionException;
use Swoft\Bean\BeanFactory;
use Hzwz\Grpc\Server\Router\Router;
use Swoft\Http\Message\Request;
use Swoft\Http\Message\Stream\Stream;
use Swoft\Stdlib\Helper\PhpHelper;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Http\Message\Request as SwoftRequest;
use Hzwz\Grpc\Server\Contract\MiddlewareInterface;
use Hzwz\Grpc\Server\Exception\GrpcServerException;
use Psr\Http\Message\RequestInterface as PsrRequestInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Hzwz\Grpc\Server\Contract\RequestHandlerInterface;
use function context;
use function method_exists;
use function sprintf;

/**
 * Class DefaultMiddleware
 * @package Hzwz\Grpc\Server\Middleware
 *
 * @Bean()
 */
class DefaultMiddleware implements MiddlewareInterface
{
  /**
   * 执行来自grpc的处理
   *
   * @param PsrRequestInterface $request
   * @param PsrRequestHandlerInterface $requestHandler
   * @return PsrRequestInterface
   * @throws GrpcServerException
   */
  public function process(PsrRequestInterface $request, RequestHandlerInterface $requestHandler): PsrResponseInterface
  {
    return $this->handler($request);
  }

  /**
   * 执行来自grpc的处理
   *
   * @param PsrRequestInterface $request
   * @return PsrResponseInterface
   * @throws GrpcServerException
   */
  private function handler(PsrRequestInterface $request): PsrResponseInterface
  {
    //$method = $request->getMethod();
    $router = $request->getUri()->getPath();

    [$status, $className] = $request->getAttribute(Request::ROUTER_ATTRIBUTE);

    if ($status !== Router::FOUND) {
      throw new GrpcServerException(sprintf('Route(%s) 不存在', $router), 500);
    }

    [$object, $method] = $this->getReflectionClassAndMethod($router, $className);

    if (!method_exists($object, $method)) {
      throw new GrpcServerException(
        sprintf('Method(%s::%s) 不存在', PsrRequestInterface::class, $method),
        404
      );
    }

    //protobuf解码
    $params = $this->parseParameters($className, $method, $request);

    //调用内部服务类
    $result = PhpHelper::call([$object, $method], ...$params);

    if (!$result instanceof Message) {
      return $this->handleResponse(null, 500);
    }

    return $this->handleResponse($result);
  }

  /**
   * 获取类实例和对应的方法
   *
   * @param string $router
   * @param string $className
   * @return array
   * @throws GrpcException
   * @throws ReflectionException
   */
  private function getReflectionClassAndMethod(string $router, string $className): array
  {
    $object = BeanFactory::getBean($className);

    $method = '';
    if (strripos($router, '/') !== false) {
      $method = substr($router, strripos($router, '/') + 1);
    }

    if (empty($method)) {
      throw new GrpcServerException("grpcMethodError: 方法错误");
    }

    return [$object, lcfirst($method)];
  }

  /**
   * 解析grpc参数
   *
   * @param string $controller
   * @param string $action
   * @param array $arguments
   * @return array
   */
  private function parseParameters(string $class, string $action, Request $request): array
  {
    $injections = [];
    $definitions = $this->getOrParse($class, $action);

    foreach ($definitions ?? [] as $definition) {
      if (! is_array($definition)) {
        throw new GrpcServerException('grpcError: 无效的方法定义.');
      }

      if (!isset($definition['type']) || !isset($definition['name'])) {
        $injections[] = null;
        continue;
      }

      $injections[] = value(function () use ($definition, $request) {
        switch ($definition['type']) {
          case 'object':
            $class = new \ReflectionClass($definition['ref']);

            $parentClass = $class->getParentClass();
            if ($parentClass && $parentClass->getName() === Message::class) {
              $stream = $request->getBody();
              return Parser::deserializeMessage([$class->getName(), null], $stream->getContents());
            }

            if (!BeanFactory::getBean($definition['ref']) && !$definition['allowsNull']) {
              throw new GrpcServerException(
                sprintf('grpcError: Argument %s invalid, object %s not found.',
                  $definition['name'], $definition['ref'])
              );
            }

            return BeanFactory::getBean($definition['ref']);
          default:
            throw new GrpcServerException('grpcError: 无效的方法定义.');
        }
      });
    }

    return $injections;
  }

  /**
   * 从元数据容器中获取方法定义。如果容器中不存在元数据，则会解析它并保存到容器中，然后返回它。
   *
   * @param string $class
   * @param string $method
   * @return array
   */
  private function getOrParse(string $class, string $method): array
  {
//        $key = $class .'::'. $method;
//        if(!empty(context()->get($key))) {
//            return context()->get($key);
//        }

    $parameters = (new \ReflectionClass($class))->getMethod($method)->getParameters();
    $definitions = [];

    foreach ($parameters as $parameter) {
      $type = $parameter->getType()->getName();
      switch ($type) {
        case 'int':
        case 'float':
        case 'string':
        case 'array':
        case 'bool':
          $definition = [
            'type' => $type,
            'name' => $parameter->getName(),
            'ref' => '',
            'allowsNull' => $parameter->allowsNull(),
          ];
          if ($parameter->isDefaultValueAvailable()) {
            $definition['defaultValue'] = $parameter->getDefaultValue();
          }
          $definitions[] = $definition;
          break;
        default:
          $definitions[] = [
            'type' => 'object',
            'name' => $parameter->getName(),
            'ref' => $parameter->getClass()->getName() ?? null,
            'allowsNull' => $parameter->allowsNull(),
          ];
          break;
      }
    }

    //context()->setMulti($key, $definitions);
    return $definitions;
  }

  /**
   * 返回响应结果
   *
   * @param ProtobufMessage|null $message
   * @param int $httpStatus
   * @param string $grpcStatus
   * @param string $grpcMessage
   * @return PsrResponseInterface
   */
  protected function handleResponse(?Message $message, int $httpStatus = 200): ResponseInterface
  {
    $response = \Swoft\Context\Context::get()->getResponse();
    return $response->withStatus($httpStatus)
      ->withBody(Stream::new(Parser::serializeMessage($message)))
      ->withAddedHeader('Content-Type', 'application/grpc')
      ->withAddedHeader('trailer', 'grpc-status, grpc-message');
  }
}
