<?php declare(strict_types=1);


namespace Hzwz\Grpc\Server\Contract;


use Hzwz\Grpc\Server\Error;

interface ResponseInterface
{
    /**
     * @param Error $error
     *
     * @return ResponseInterface
     */
    public function setError(Error $error): ResponseInterface;

    /**
     * @param $data
     *
     * @return ResponseInterface
     */
    public function setData($data): ResponseInterface;

    /**
     * @param string $content
     *
     * @return ResponseInterface
     */
    public function setContent(string $content): ResponseInterface;

    /**
     * @return bool
     */
    public function send(): bool;

    /**
     * @return mixed
     */
    public function getData();
}
