<?php declare(strict_types=1);


namespace Hzwz\Grpc\Server\Contract;


use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Response;

interface ResponseEmitterInterface
{
    public function emit(ResponseInterface $response, Response $swooleResponse);
}
