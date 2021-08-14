<?php declare(strict_types=1);


namespace Hzwz\Grpc\Server\Middleware;

use Hzwz\Grpc\Server\Exception\GrpcServerException;
use Hzwz\Grpc\Server\Contract\MiddlewareInterface;
use Hzwz\Grpc\Server\Contract\GrpcRequestHandlerInterface;
use Hzwz\Grpc\Server\GrpcHelper;
use Hzwz\Grpc\Server\GrpcServiceHandler;
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

    //添加用户中间件
    $grpcMiddlewares = MiddlewareRegister::getMiddlewares($className, GrpcHelper::getRequestClassMethod($grpcRouter));
    if (!empty($grpcMiddlewares) && $requestHandler instanceof GrpcServiceHandler) {
      $requestHandler->insertMiddlewares($grpcMiddlewares);
    }

    return $requestHandler->handle($request);
  }
}
