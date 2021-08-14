<?php declare(strict_types=1);


namespace Hzwz\Grpc\Server;

use Hzwz\Grpc\Server\Middleware\DefaultMiddleware;
use Hzwz\Grpc\Server\Middleware\UserMiddleware;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\BeanFactory;
use Swoft\Concern\AbstractDispatcher;
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

        $swooleResponse = $response->getCoResponse();

        try {
            $handler = GrpcServiceHandler::new($this->requestMiddleware(), $this->defaultMiddleware);

            $response = $handler->handle($request);
            $this->trailerSet($swooleResponse);
        } catch (\Throwable $e) {
            Log::error("GrpcResponseError", [
                'errorMsg'   => $e->getMessage(),
                'errorSite'  => $e->getFile(),
                'errorLine'  => $e->getLine(),
                'errorTrace' => $e->getTraceAsString(),
                'time'       => date('Y-m-d H:i:s')
            ]);

            $response->withAddedHeader('Content-Type', 'application/grpc');
            $response->withAddedHeader('trailer', 'grpc-status, grpc-message');
            $this->trailerSet($swooleResponse, $e);
        } finally {
            \bean(ResponseEmitter::class)->emit($response, $swooleResponse);

            \Swoft::trigger(SwoftEvent::COROUTINE_DEFER);
            \Swoft::trigger(SwoftEvent::COROUTINE_COMPLETE);
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
     * @return void
     */
    protected function trailerSet(\Swoole\Http\Response $swooleResponse, $throw = null)
    {
        if (!is_null($throw)) {
            $swooleResponse->trailer('grpc-status',  $throw->getCode() <= 0 ? '500' : (string)$throw->getCode());
            $swooleResponse->trailer('grpc-message', $throw->getMessage());
        } else {
            $swooleResponse->trailer('grpc-status', '200');
            $swooleResponse->trailer('grpc-message', '');
        }
    }
}
