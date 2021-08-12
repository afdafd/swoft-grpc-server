<?php declare(strict_types=1);


namespace Hzwz\Grpc\Server\Router;

/**
 * Class RouteRegister
 * @package Hzwz\Grpc\Server\Router
 */
class RouteRegister
{
    /**
     * @var array
     *
     * @example['prefix' => $className]
     */
    private static $services = [];

    /**
     * @var array
     *
     * @example [$className => 'prefix']
     */
    private static $serviceClassNames = [];

    /**
     * @param string $interface
     * @param string $version
     * @param string $className
     */
    public static function register(string $prefix, string $className): void
    {
        self::$services[$prefix] = $className;

        // Record classNames
        self::$serviceClassNames[$className] = $prefix;
    }

    /**
     * @param string $className
     *
     * @return bool
     */
    public static function hasRouteByClassName(string $className): bool
    {
        return isset(self::$serviceClassNames[$className]);
    }

    /**
     * @param Router $router
     */
    public static function registerRoutes(Router $router): void
    {
        foreach (self::$services as $prefix => $className) {
            $router->addRoute($prefix, $className);
        }
    }
}
