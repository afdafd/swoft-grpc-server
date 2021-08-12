<?php declare(strict_types=1);

namespace Hzwz\Grpc\Server;

use function bean;
use Hzwz\Grpc\Server\Contract\ResponseInterface;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Concern\PrototypeTrait;
use Hzwz\Grpc\Server\Error;

/**
 * Class Response
 * @package Hzwz\Grpc\Server
 */
class Response implements ResponseInterface
{
    use PrototypeTrait;

    /**
     * @var self
     */
    protected $response;

    /**
     * @var int
     */
    protected $fd = 0;

    /**
     * @var int
     */
    protected $reactorId = 0;

    /**
     * @var string
     */
    protected $content = '';

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var Error
     */
    protected $error;

    /**
     * @param Server $server
     * @param int    $fd
     * @param int    $reactorId
     *
     * @return Response
     */
    public static function new(\Swoole\Http\Response $response = null): self
    {
        $instance = self::__instance();
        $instance->response = $response;
        return $instance;
    }

    /**
     * @param Error $error
     *
     * @return ResponseInterface
     */
    public function setError(Error $error): ResponseInterface
    {
        $this->error = $error;
        return $this;
    }

    /**
     * @param $data
     *
     * @return ResponseInterface
     */
    public function setData($data): ResponseInterface
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param string $content
     *
     * @return ResponseInterface
     */
    public function setContent(string $content): ResponseInterface
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return bool
     * @throws RpcException
     */
    public function send(): bool
    {
        $this->prepare();

        vdump("发送数据");
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @throws RpcException
     */
    protected function prepare(): void
    {
      /*  /* @var Packet $packet/
        $packet = bean('rpcServerPacket');

        if ($this->error === null) {
            $this->content = $packet->encodeResponse($this->data);
            return;
        }

        $code    = $this->error->getCode();
        $message = $this->error->getMessage();
        $data    = $this->error->getData();*/

        $this->content = 'response data';
    }
}
