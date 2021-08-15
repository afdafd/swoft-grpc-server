<?php declare(strict_types=1);

namespace Hzwz\Grpc\Server\Middleware;


use Hzwz\Grpc\Server\GrpcHelper;
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
use Hzwz\Grpc\Server\Contract\GrpcRequestHandlerInterface;
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
  public function process(PsrRequestInterface $request, GrpcRequestHandlerInterface $requestHandler): PsrResponseInterface
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
    $router = $request->getUri()->getPath();

    [$status, $className] = $request->getAttribute(Request::ROUTER_ATTRIBUTE);

    if ($status !== Router::FOUND) {
      throw new GrpcServerException(sprintf('Route(%s) 不存在', $router), 500);
    }

    [$object, $method] = $this->getReflectionClassAndMethod($router, $className);

    if (!method_exists($object, $method)) {
      throw new GrpcServerException(
        sprintf('Method(%s::%s) 不存在', $router, $method), 500);
    }

    //调用内部服务类
    $params = context()->get('grpcPareDataAfter');
    $result = PhpHelper::call([$object, $method], ...$params);
    context()->unset('grpcPareDataAfter');

    if (!$result instanceof Message) {
      throw new GrpcServerException(
        sprintf('响应类(%s) 不是Message类型', (string)$result), 500);
    }

    //返回响应
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
    $method = GrpcHelper::getRequestClassMethod($router);

    return [$object, $method];
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
