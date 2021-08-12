<?php declare(strict_types=1);


namespace Swoft\Grpc\Server\Swoole;

use Swoft\Grpc\Middleware\MiddlewareRegister;
use Swoft\Grpc\Server\Exception\GrpcServerException;
use Swoft\Grpc\Server\Router\Router;
use Swoft\Grpc\Server\Router\RouteRegister;
use Swoft\Bean\BeanFactory;
use Swoft\SwoftEvent;
use Swoft\Event\Annotation\Mapping\Listener;
use Swoft\Event\EventHandlerInterface;
use Swoft\Event\EventInterface;

/**
 * Class AppInitCompleteListener
 * @package Swoft\Grpc\Server\Swoole
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
