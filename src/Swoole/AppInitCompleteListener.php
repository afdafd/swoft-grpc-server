<?php declare(strict_types=1);


namespace Hzwz\Grpc\Server\Swoole;

use Hzwz\Grpc\Server\Middleware\MiddlewareRegister;
use Hzwz\Grpc\Server\Exception\GrpcServerException;
use Hzwz\Grpc\Server\Router\Router;
use Hzwz\Grpc\Server\Router\RouteRegister;
use Swoft\Bean\BeanFactory;
use Swoft\SwoftEvent;
use Swoft\Event\Annotation\Mapping\Listener;
use Swoft\Event\EventHandlerInterface;
use Swoft\Event\EventInterface;

/**
 * Class AppInitCompleteListener
 * @package Hzwz\Grpc\Server\Swoole
 *
 * @Listener(event=SwoftEvent::APP_INIT_COMPLETE)
 */
class AppInitCompleteListener implements EventHandlerInterface
{
    /**
     * @param EventInterface $event
     * @throws GrpcServerException
     */
    public function handle(EventInterface $event): void
    {
        /* @var Router $router */
        $router = BeanFactory::getBean('grpcServerRouter');

        //路由注册
        RouteRegister::registerRoutes($router);

        //中间件注册
        MiddlewareRegister::register();
    }

}
