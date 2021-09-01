<?php declare(strict_types=1);


namespace Hzwz\Grpc\Server;

use Hzwz\Grpc\Server\Middleware\DefaultMiddleware;
use Hzwz\Grpc\Server\Middleware\UserMiddleware;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\BeanFactory;
use Swoft\Concern\AbstractDispatcher;
use Swoft\Context\Context;
use Swoft\Http\Message\Request;
use Swoft\Http\Message\Response;
use Swoft\Log\Helper\Log;
use Hzwz\Grpc\Server\GrpcServiceHandler;
use Swoft\Swoft;
use Swoft\SwoftEvent;

/**
 * Class GrpcServiceDispatcher
 * @package Hzwz\Grpc\Server
 *
 * @Bean()
 */
class GrpcServiceDispatcher extends AbstractDispatcher
{
    /**
     * @var string
     */
    protected $defaultMiddleware = DefaultMiddleware::class;

    /**
     * @param array $params
     */
    public function dispatch(...$params): void
    {
        /**
         * @var Request  $request
         * @var Response $response
         */
        [$request, $response] = $params;

        try {
            $handler = GrpcServiceHandler::new($this->requestMiddleware(), $this->defaultMiddleware);
            $response = $handler->handle($request);
            $swooleResponse = $this->trailerSet($response->getCoResponse());
        } catch (\Throwable $e) {
          $response = \Swoft\Context\Context::get()->getResponse()
            ->withAddedHeader('Content-Type', 'application/grpc')
            ->withAddedHeader('trailer', 'grpc-status, grpc-message');
            $swooleResponse = $this->trailerSet($response->getCoResponse(), $e);
        } finally {
            \bean(ResponseEmitter::class)->emit($response, $swooleResponse);
            $this->clearHandle();

            //$response->quickSend($response);
        }
    }

    /**
     * @return array
     */
    public function preMiddleware(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function afterMiddleware(): array
    {
        return [
          UserMiddleware::class
        ];
    }

    /**
     * trailce设置
     *
     * @param \Swoole\Http\Response $swooleResponse
     * @param null $throw
     * @return \Swoole\Http\Response
     */
    protected function trailerSet(\Swoole\Http\Response $swooleResponse, $throw = null): \Swoole\Http\Response
    {
        if (!is_null($throw)) {
            $swooleResponse->trailer('grpc-status',  $throw->getCode() <= 0 ? '500' : (string)$throw->getCode());
            $swooleResponse->trailer('grpc-message', $throw->getMessage());

          Log::error("GrpcResponseError", [
            'errorMsg'   => $throw->getMessage(),
            'errorSite'  => $throw->getFile(),
            'errorLine'  => $throw->getLine(),
            'time'       => date('Y-m-d H:i:s')
          ]);
        } else {
            $swooleResponse->trailer('grpc-status', '0');
            $swooleResponse->trailer('grpc-message', '');
        }

        return $swooleResponse;
    }

  /**
   * 清除处理
   */
    protected function clearHandle()
    {
      \Swoft::trigger(SwoftEvent::COROUTINE_DEFER);
      \Swoft::trigger(SwoftEvent::COROUTINE_COMPLETE);
    }
}
