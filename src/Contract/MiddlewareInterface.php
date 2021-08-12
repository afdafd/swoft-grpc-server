<?php declare(strict_types=1);


namespace Hzwz\Grpc\Server\Contract;

use Psr\Http\Message\RequestInterface as PsrRequestInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Hzwz\Grpc\Server\Contract\RequestHandlerInterface;

interface MiddlewareInterface
{
    /**
     * 程序处理
     *
     * @param PsrRequestInterface $request
     * @param RequestHandlerInterface $requestHandler
     * @return PsrResponseInterface
     */
    public function process(PsrRequestInterface $request, RequestHandlerInterface $requestHandler): PsrResponseInterface;
}
