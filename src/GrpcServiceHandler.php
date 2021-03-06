<?php declare(strict_types=1);


namespace Hzwz\Grpc\Server;

use Swoft\Bean\BeanFactory;
use Swoft\Bean\Annotation\Mapping\Bean;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Swoft\Bean\Concern\PrototypeTrait;
use Hzwz\Grpc\Server\Contract\MiddlewareInterface;
use Hzwz\Grpc\Server\Exception\GrpcServerException;
use Hzwz\Grpc\Server\Contract\GrpcRequestHandlerInterface;

/**
 * Class GrpcServiceHandler
 * @package Hzwz\Grpc\Server
 *
 * @Bean()
 */
class GrpcServiceHandler implements GrpcRequestHandlerInterface
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
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
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
            throw new GrpcServerException('grpcMiddlewareError: 插入的offset错误，所插入的值不能大于已存在的： ' . $this->offset);
        }

        //添加用户的自定义中间件
        array_splice($this->middlewares, $offset, 0, $middlewares);
    }
}
