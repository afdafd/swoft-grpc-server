<?php declare(strict_types=1);

namespace Hzwz\Grpc\Server\Contract;

/**
 * Class RouterInterface
 *
 * @since 2.0
 */
interface RouterInterface extends \Swoft\Contract\RouterInterface
{
    /**
     * @param string $interface
     * @param string $version
     * @param string $className
     */
    public function addRoute(string $prefix, string $className): void;

    /**
     * @param string $version
     * @param string $interface
     *
     * @return array
     */
    public function match(string $prefix): array;
}
