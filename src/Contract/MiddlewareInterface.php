<?php declare(strict_types=1);


namespace Hzwz\Grpc\Server\Contract;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Hzwz\Grpc\Server\Contract\GrpcRequestHandlerInterface;

interface MiddlewareInterface
{
    /**
     * 程序处理
     *
     * @param RequestInterface $request
     * @param GrpcRequestHandlerInterface $requestHandler
     * @return ResponseInterface
     */
    public function process(RequestInterface $request, GrpcRequestHandlerInterface $requestHandler): ResponseInterface;
}
