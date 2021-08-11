<?php declare(strict_types=1);


namespace Swoft\Grpc\Server;


use Swoft\Grpc\Server\Contract\ResponseEmitterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UploadedFileInterface;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoole\Http\Response;

/**
 * Class ResponseEmitter
 * @package Swoft\Grpc\Server
 *
 * @Bean()
 */
class ResponseEmitter implements ResponseEmitterInterface
{
    /**
     * 发送swoole响应消息
     *
     * @param ResponseInterface $response
     * @param Response $swooleResponse
     * @param bool $withContent
     */
    public function emit(ResponseInterface $response, Response $swooleResponse, bool $withContent = true)
    {
        $this->buildSwooleResponse($swooleResponse, $response);

        if ($withContent) {
            $swooleResponse->end($response->getBody()->getContents());
        } else {
            $swooleResponse->end();
        }
    }

    /**
     * 构建swoole响应
     *
     * @param Response $swooleResponse
     * @param ResponseInterface $response
     */
    protected function buildSwooleResponse(Response $swooleResponse, ResponseInterface $response): void
    {
        //Headers
        foreach ($response->getHeaders() as $key => $value) {
            $swooleResponse->header($key, implode(';', $value));
        }

        //Status code
        $swooleResponse->status($response->getStatusCode(), $response->getReasonPhrase());

       //Cookies
        if (method_exists($response, 'getCookies')) {
            foreach ((array) $response->getCookies() as $domain => $paths) {
                foreach ($paths ?? [] as $path => $item) {
                    foreach ($item ?? [] as $name => $cookie) {
                        if ($this->isMethodsExists($cookie, [
                            'isRaw', 'getValue', 'getName', 'getExpiresTime', 'getPath', 'getDomain', 'isSecure', 'isHttpOnly', 'getSameSite',
                        ])) {
                            $value = $cookie->isRaw() ? $cookie->getValue() : rawurlencode($cookie->getValue());
                            $swooleResponse->rawcookie($cookie->getName(), $value, $cookie->getExpiresTime(), $cookie->getPath(), $cookie->getDomain(), $cookie->isSecure(), $cookie->isHttpOnly(), (string) $cookie->getSameSite());
                        }
                    }
                }
            }
        }

        //Trailers
        if (method_exists($response, 'getTrailers') && method_exists($swooleResponse, 'trailer')) {
            foreach ($response->getTrailers() ?? [] as $key => $value) {
                $swooleResponse->trailer($key, $value);
            }
        }
    }

    /**
     * 判断方式是否存在
     *
     * @param object $object
     * @param array $methods
     * @return bool
     */
    protected function isMethodsExists(object $object, array $methods): bool
    {
        foreach ($methods as $method) {
            if (! method_exists($object, $method)) {
                return false;
            }
        }
        return true;
    }
}
