<?php declare(strict_types=1);


namespace Swoft\Grpc\Middleware;

use Swoft\Grpc\Server\Exception\GrpcServerException;
use Swoft\Grpc\Server\Contract\MiddlewareInterface;
use Swoft\Grpc\Server\Contract\RequestHandlerInterface;
use Swoft\Grpc\Server\Router\Router;
use Psr\Http\Message\RequestInterface as PsrRequestInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Swoft\Bean\Annotation\Mapping\Bean;

/**
 * Class RouterValidatorMiddleware
 * @package App\Grpc\Middleware
 *
 * @Bean()
 */
class RouterValidatorMiddleware implements MiddlewareInterface
{
    /**
     * 校验路由
     *
     * @param PsrRequestInterface $request
     * @param RequestHandlerInterface $requestHandler
     * @return PsrResponseInterface
     * @throws GrpcException
     */
    public function process(PsrRequestInterface $request, RequestHandlerInterface $requestHandler): PsrResponseInterface
    {
        $router = \bean('grpcServerRouter');

        $grpcRouter = $request->getUri()->getPath();
        list($status, ,) = $router->match($grpcRouter);

        if (Router::FOUND !== $status) {
            throw new GrpcServerException("grpcError: {$grpcRouter} 路由不存在");
        }

        unset($router);
        return $requestHandler->handle($request);
    }
}
