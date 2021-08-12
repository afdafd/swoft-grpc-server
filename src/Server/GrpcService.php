<?php declare(strict_types=1);

namespace Swoft\Grpc\Server;

use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Server\Exception\ServerException;
use Swoft\Server\Server;

/**
 * Class GrpcService
 * @package Swoft\Grpc\Server
 *
 * @Bean(name="grpcServer")
 */
class GrpcService extends Server
{
    /**
     * @var string
     */
    protected static $serverType = 'GRPC';

    /**
     * Default port
     *
     * @var int
     */
    protected $port = 95001;

    /**
     * @var string
     */
    protected $pidName = 'swoft-grpc';

    /**
     * @var string
     */
    protected $commandFile = '@runtime/swoft-grpc.command';

    /**
     * @var string
     */
    protected $pidFile = '@runtime/swoft-grpc.pid';

    /**
     * Start server
     *
     * @throws ServerException
     */
    public function start(): void
    {
        $this->swooleServer = new \Swoole\Http\Server($this->host, $this->port, $this->mode, $this->type);
        $this->startSwoole();
    }
}
