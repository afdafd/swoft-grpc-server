<?php declare(strict_types=1);


namespace Hzwz\Grpc\Server;

use Hzwz\Grpc\Server\Swoole\RequestListener;
use Swoft\Helper\ComposerJSON;
use Swoft\Server\SwooleEvent;
use Swoft\SwoftComponent;
use function bean;

/**
 * Class AutoLoader
 * @package Hzwz\Grpc\Server
 */
class AutoLoader extends SwoftComponent
{
    /**
     * @return array
     */
    public function getPrefixDirs(): array
    {
        return [
            __NAMESPACE__ => __DIR__,
        ];
    }

    /**
     * @return array
     */
    public function metadata(): array
    {
        $jsonFile = dirname(__DIR__) . '/composer.json';

        return ComposerJSON::open($jsonFile)->getMetadata();
    }

    /**
     * @return array
     */
    public function beans(): array
    {
        return [
            'grpcServer' => [
                'class' => GrpcService::class,
                'on'    => [
                    SwooleEvent::REQUEST => bean(RequestListener::class),
                ]
            ]
        ];
    }
}
