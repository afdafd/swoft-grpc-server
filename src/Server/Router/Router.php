<?php declare(strict_types=1);

namespace Swoft\Grpc\Server\Router;


use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Grpc\Server\Contract\RouterInterface;

/**
 * Class Router
 * @package Swoft\Grpc\Server\Router
 *
 * @Bean(name="grpcServerRouter")
 */
class Router implements RouterInterface
{
    /**
     * @var array
     *
     * @example
     * [
     *    'prefix' => $className
     * ]
     */
    private $routes = [];

    /**
     * @param string $prefix
     * @param string $className
     */
    public function addRoute(string $prefix, string $className): void
    {
        $this->routes[$prefix] = $className;
    }

    /**
     * @param string $prefix
     * @return array
     */
    public function match(string $prefix): array
    {
        if (isset($this->routes[$prefix])) {
            return [self::FOUND, $this->routes[$prefix]];
        }

        return [self::NOT_FOUND, ''];
    }

    /**
     * @return array
     */
    public function getArrayRoutes(): array
    {
        return $this->routes;
    }
}
