<?php declare(strict_types=1);


namespace Swoft\Grpc\Server\Contract;

use Psr\Http\Message\RequestInterface as PsrRequestInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

/**
 * Interface RequestHandlerInterface
 * @package App\Grpc\Server\Contract
 */
interface RequestHandlerInterface
{
    /**
     * 处理请求并且返回响应
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(PsrRequestInterface $request): PsrResponseInterface;
}
