<?php declare(strict_types=1);


namespace Hzwz\Grpc\Server\Middleware;

use Google\Protobuf\Internal\Message;
use Google\Protobuf\Internal\RepeatedField;
use Hzwz\Grpc\Server\Exception\GrpcServerException;
use Hzwz\Grpc\Server\Contract\MiddlewareInterface;
use Hzwz\Grpc\Server\Contract\GrpcRequestHandlerInterface;
use Hzwz\Grpc\Server\GrpcHelper;
use Hzwz\Grpc\Server\GrpcServiceHandler;
use Hzwz\Grpc\Server\Parser;
use Hzwz\Grpc\Server\Router\Router;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Http\Message\Request;

/**
 * Class UserMiddleware
 * @package Hzwz\Grpc\Server\Middleware
 *
 * @Bean()
 */
class UserMiddleware implements MiddlewareInterface
{
  /**
   * 用户发起的grpc服务请求的基本处理
   *
   * @param RequestInterface $request
   * @param GrpcRequestHandlerInterface $requestHandler
   * @return ResponseInterface
   */
  public function process(RequestInterface $request, GrpcRequestHandlerInterface $requestHandler): ResponseInterface
  {
    $grpcServerRouter = \bean('grpcServerRouter');

    $grpcRouter = $request->getUri()->getPath();
    $grpcServerRouter = $grpcServerRouter->match($grpcRouter);

    $request = $request->withAttribute(Request::ROUTER_ATTRIBUTE, $grpcServerRouter);
    context()->setRequest($request);

    [$status, $className] = $grpcServerRouter;

    if ($status !== Router::FOUND) {
      return $requestHandler->handle($request);
    }

    //protobuf解码
    $params = $this->parseParameters($className, GrpcHelper::getRequestClassMethod($grpcRouter), $request);

    //请求类字段属性和值处理
    $request = $this->requestClassPropertiesHandle($params, $request);

    //添加用户中间件
    $grpcMiddlewares = MiddlewareRegister::getMiddlewares($className, GrpcHelper::getRequestClassMethod($grpcRouter));
    if (!empty($grpcMiddlewares) && $requestHandler instanceof GrpcServiceHandler) {
      $requestHandler->insertMiddlewares($grpcMiddlewares);
    }

    return $requestHandler->handle($request);
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

    foreach ($definitions as $definition) {
      if (!is_array($definition)) {
        throw new GrpcServerException('grpcError: 无效的方法定义.');
      }

      if (!isset($definition['name'])) {
        $injections[] = null;
        continue;
      }

      $injections[] = value(function() use($definition, $request) {
        $class = new \ReflectionClass($definition['ref']);

        $parentClass = $class->getParentClass();
        if ($parentClass && $parentClass->getName() === Message::class) {
          $stream = $request->getBody();
          return Parser::deserializeMessage([$class->getName(), null], $stream->getContents());
        }

        throw new GrpcServerException('grpcError: 无效的方法定义.');
      });
    }

    return $injections;
  }

  /**
   * 从元数据容器中获取方法定义
   *
   * @param string $class
   * @param string $method
   * @return array
   */
  private function getOrParse(string $class, string $method): array
  {
    $parameters = (new \ReflectionClass($class))->getMethod($method)->getParameters();

    $definitions = [];
    foreach ($parameters as $parameter) {
      $definitions[] = [
        'name' => $parameter->getName(),
        'ref' => $parameter->getClass()->getName() ?? null,
        'allowsNull' => $parameter->allowsNull(),
      ];
    }

    return $definitions;
  }

  /**
   * 类的属性字段和值处理
   *
   * @param $params
   * @param $request
   * @return void
   * @throws ReflectionException
   */
  private function requestClassPropertiesHandle($params, $request)
  {
    $requestData = [];

    foreach ($params as $key => $objName) {
      $obj = new \ReflectionClass($objName);

      foreach ($obj->getProperties() as $property) {
        $property->setAccessible(true);

        //略过静态属性
        if ($property->isStatic()) {
          continue;
        }

        $field = GrpcHelper::camelize($property->getName());
        $propertyValue = $objName->{'get'.ucfirst($field)}();

        //Repeated类型字段处理
        if ($propertyValue instanceof RepeatedField) {
          $value = [];

          foreach ($propertyValue->getIterator() as $iterator) {
            $value[] = $iterator->current();
          }

          $propertyValue = $value;
        }

        $requestData[$property->getName()] = $propertyValue;
      }
    }

    $request = $request->withParsedBody($requestData)->withQueryParams($requestData);
    context()->setRequest($request);
    context()->setMulti(['grpcPareDataAfter' => $params]);

    return $request;
  }
}
