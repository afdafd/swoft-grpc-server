<?php declare(strict_types=1);


namespace Hzwz\Grpc\Server\Middleware;

use Hzwz\Grpc\Server\Exception\GrpcServerException;
use Hzwz\Grpc\Server\Contract\MiddlewareInterface;
use Hzwz\Grpc\Server\Contract\RequestHandlerInterface;
use Hzwz\Grpc\Server\GrpcHelper;
use Hzwz\Grpc\Server\GrpcServiceHandler;
use Hzwz\Grpc\Server\Router\Router;
use Psr\Http\Message\RequestInterface as PsrRequestInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
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
   * 用户中间件处理
   *
   * @param PsrRequestInterface $request
   * @param RequestHandlerInterface $requestHandler
   * @return PsrResponseInterface
   * @throws GrpcException
   */
  public function process(PsrRequestInterface $request, RequestHandlerInterface $requestHandler): PsrResponseInterface
  {
    $grpcRouter = $request->getUri()->getPath();
    $method = GrpcHelper::getRequestClassMethod($grpcRouter);

    $grpcServerRouter = \bean('grpcServerRouter');
    $grpcServerRouter = $grpcServerRouter->match($grpcRouter);
    $request = $request->withAttribute(Request::ROUTER_ATTRIBUTE, $grpcServerRouter);

    [$status, $className] = $grpcServerRouter;

    if ($status !== Router::FOUND) {
      return $requestHandler->handle($request);
    }

    //添加用户中间件
    $middlewares = MiddlewareRegister::getMiddlewares($className, $method);
    if (!empty($middlewares) && $requestHandler instanceof GrpcServiceHandler) {
      $requestHandler->insertMiddlewares($middlewares);
    }

    return $requestHandler->handle($request);
  }
}
