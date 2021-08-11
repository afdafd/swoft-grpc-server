<?php declare(strict_types=1);


namespace Swoft\Grpc\Command;

use Swoft\Console\Annotation\Mapping\Command;
use Swoft\Console\Annotation\Mapping\CommandMapping;
use Swoft\Console\Annotation\Mapping\CommandOption;
use Swoft\Server\Command\BaseServerCommand;
use Swoft\Server\Exception\ServerException;
use Swoft\Grpc\Server\GrpcService;

/**
 * Class GrpcServerCommand
 * @package Swoft\Grpc\Server\Command
 *
 * @Command("grpc", coroutine=false, desc="Provide some commands to manage swoft GRPC server")
 *
 * @example
 *  {groupName}:start     Start the grpc server
 *  {groupName}:stop      Stop the grpc server
 */
class GrpcServerCommand extends BaseServerCommand
{
    /**
     * 开启http服务
     *
     * @CommandMapping(usage="{fullCommand} [-d|--daemon]")
     * @CommandOption("daemon", short="d", desc="Run server on the background")
     *
     * @throws ServerException
     * @example
     *  {fullCommand}
     *  {fullCommand} -d
     *
     */
    public function start(): void
    {
        $server = $this->createServer();
        $this->showServerInfoPanel($server);

        $server->start();
    }

    /**
     * 重启工作进程
     *
     * @CommandMapping(usage="{fullCommand} [-t]")
     * @CommandOption("t", desc="Only to reload task processes, default to reload worker and task")
     */
    public function reload(): void
    {
        $server = $this->createServer();

        // Reload server
        $this->reloadServer($server);
    }

    /**
     * 停止当前运行的服务
     *
     * @CommandMapping()
     */
    public function stop(): void
    {
        $server = $this->createServer();

        // Check if it has started
        if (!$server->isRunning()) {
            output()->writeln('<error>The GRPC server is not running! cannot stop.</error>');
            return;
        }

        // Do stopping.
        $server->stop();
    }

    /**
     * 重启http服务
     *
     * @CommandMapping(usage="{fullCommand} [-d|--daemon]",)
     * @CommandOption("daemon", short="d", desc="Run server on the background")
     *
     * @example
     *  {fullCommand}
     *  {fullCommand} -d
     */
    public function restart(): void
    {
        $server = $this->createServer();

        // Restart server
        $this->restartServer($server);
    }

    /**
     * 获取grpc服务
     *
     * @return GrpcService
     */
    private function createServer(): GrpcService
    {
        $script  = input()->getScriptFile();
        $command = $this->getFullCommand();

        /** @var GrpcService $server */
        $server = bean('grpcServer');
        $server->setScriptFile(\Swoft::app()->getPath($script));
        $server->setFullCommand($command);

        return $server;
    }
}
