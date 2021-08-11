<?php declare(strict_types=1);


namespace Swoft\Grpc\Server;

use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\BeanFactory;
use Swoft\Bean\Concern\PrototypeTrait;
use Swoft\Grpc\Server\Contract\MiddlewareInterface;
use Swoft\Grpc\Server\Contract\RequestInterface;
use Swoft\Grpc\Server\Contract\ResponseInterface;
use Psr\Http\Message\RequestInterface as PsrRequestInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Swoft\Grpc\Server\Exception\GrpcServerException;
use Swoft\Grpc\Server\Contract\RequestHandlerInterface;

/**
 * Class GrpcServiceHandler
 * @package Swoft\Grpc\Server
 *
 * @Bean()
 */
class GrpcServiceHandler implements RequestHandlerInterface
{
    use PrototypeTrait;

    /**
     * @var array
     */
    protected $middlewares = [];

    /**
     * @var string
     */
    protected $defaultMiddleware = '';

    /**
     * Current offset
     *
     * @var int
     */
    protected $offset = 0;

    /**
     * @param array  $middlewares
     * @param string $defaultMiddleware
     *
     * @return self
     *
     */
    public static function new(array $middlewares, string $defaultMiddleware): self
    {
        $instance = self::__instance();

        $instance->offset = 0;

        $instance->middlewares       = $middlewares;
        $instance->defaultMiddleware = $defaultMiddleware;

        return $instance;
    }

    /**
     * 中间件处理
     *
     * @param PsrRequestInterface $request
     * @return PsrResponseInterface
     */
    public function handle(PsrRequestInterface $request): PsrResponseInterface
    {
        //处理路由请求默认中间件
        $middleware = $this->middlewares[$this->offset] ?? $this->defaultMiddleware;

        /* @var MiddlewareInterface $bean */
        $bean = BeanFactory::getBean($middleware);

        //下一个中间件
        $this->offset++;

        return $bean->process($request, $this);
    }

    /**
     * Insert middleware at offset
     *
     * @param array    $middlewares
     * @param int|null $offset
     *
     * @throws RpcServerException
     */
    public function insertMiddlewares(array $middlewares, int $offset = null): void
    {
        $offset = $offset ?? $this->offset;
        if ($offset > $this->offset) {
            throw new GrpcServerException('grpcMiddlewareError: Insert middleware offset must more than ' . $this->offset);
        }

        // Insert middlewares
        array_splice($this->middlewares, $offset, 0, $middlewares);
    }
}
