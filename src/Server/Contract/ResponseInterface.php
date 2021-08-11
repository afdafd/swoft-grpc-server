<?php declare(strict_types=1);


namespace Swoft\Grpc\Server\Contract;


use Swoft\Grpc\Error;

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
