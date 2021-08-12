<?php declare(strict_types=1);

namespace Hzwz\Grpc\Server\Swoole;


use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoft\Context\Context;
use Swoft\Bean\BeanFactory;
use Hzwz\Grpc\Server\ServiceContext;
use Psr\Http\Message\ResponseInterface;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use Hzwz\Grpc\Server\GrpcServiceDispatcher;
use Swoft\Server\Contract\RequestInterface;
use Swoft\Http\Message\Request as PsrRequest;
use Swoft\Http\Message\Response as PsrResponse;
use Swoft\Http\Message\Request as SwoftRequest;

/**
 * Class RequestListener
 * @package Hzwz\Grpc\Server\Swoole
 *
 * @Bean()
 */
class RequestListener implements RequestInterface
{
    /**
     * @Inject()
     * @var GrpcServiceDispatcher
     */
    protected $dispatcher;

    /**
     * 处理请求
     *
     * @param Request $request
     * @param Response $response
     */
    public function onRequest(Request $request, Response $response): void
    {
        [$psr7Request, $psr7Response] = $this->initRequestAndResponse($request, $response);
        $this->dispatcher->dispatch($psr7Request, $psr7Response);
    }

    /**
     * 初始化
     *
     * @param Request $request
     * @param Response $response
     * @return array
     */
    protected function initRequestAndResponse(Request $request, Response $response): array
    {
        $psr7Request = PsrRequest::new($request);
        $psr7Response = PsrResponse::new($response);

        //设置上下文
        Context::set(
            ServiceContext::new($psr7Request, $psr7Response)
        );

        return [$psr7Request, $psr7Response];
    }
}
