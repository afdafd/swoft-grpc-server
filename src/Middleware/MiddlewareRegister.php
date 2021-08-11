<?php declare(strict_types=1);

namespace Swoft\Grpc\Middleware;

use Swoft\Grpc\Server\Exception\GrpcServerException;
use Swoft\Grpc\Server\Router\RouteRegister;
use function array_unique;

/**
 * Class MiddlewareRegister
 * @package App\Grpc\Middleware
 */
class MiddlewareRegister
{
    /**
     * 全部中间件——类级中间件和方法级中间件
     *
     * @var array
     *
     * @example
     * [
     *     'className' => [
     *          'class' => [
     *              'middlewareName',
     *              'middlewareName',
     *              'middlewareName'
     *          ]
     *          'methods' => [
     *              'actionName' => [
     *                  'middlewareName',
     *                  'middlewareName',
     *                  'middlewareName'
     *              ]
     *          ]
     *     ]
     * ]
     */
    private static $middlewares = [];

    /**
     * 处理程序中间件
     *
     * @var array
     *
     * @example
     * [
     *      'className' => [
     *          'methodName' => [
     *              'middlewareName',
     *              'middlewareName',
     *          ]
     *      ]
     * ]
     */
    private static $handlerMiddlewares = [];

    /**
     * 注册类级中间件
     *
     * @param string $name      middleware name
     * @param string $className class name
     *
     * @return void
     */
    public static function registerByClassName(string $name, string $className): void
    {
        $middlewares   = self::$middlewares[$className]['class'] ?? [];
        $middlewares[] = $name;

        self::$middlewares[$className]['class'] = array_unique($middlewares);
    }

    /**
     * 注册方法级中间件
     *
     * @param string $name
     * @param string $className
     * @param string $methodName
     *
     * @return void
     */
    public static function registerByMethodName(string $name, string $className, string $methodName): void
    {
        $middlewares   = self::$middlewares[$className]['methods'][$methodName] ?? [];
        $middlewares[] = $name;

        self::$middlewares[$className]['methods'][$methodName] = array_unique($middlewares);
    }

    /**
     * 注册处理程序中间件
     */
    public static function register(): void
    {
        foreach (self::$middlewares as $className => $middlewares) {
            if (!RouteRegister::hasRouteByClassName($className)) {
                throw new GrpcServerException(sprintf('`@Service` is undefined on class(%s)', $className));
            }

            $classMiddlewares  = $middlewares['class'] ?? [];
            $methodMiddlewares = $middlewares['methods'] ?? [];

            foreach ($methodMiddlewares as $methodName => $oneMethodMiddlewares) {
                if (!empty($oneMethodMiddlewares)) {
                    $allMiddlewares = array_merge($classMiddlewares, $oneMethodMiddlewares);

                    self::$handlerMiddlewares[$className][$methodName] = array_unique($allMiddlewares);
                }
            }
        }
    }

    /**
     * 获取中间件
     *
     * @param string $className
     * @param string $methodName
     *
     * @return array
     */
    public static function getMiddlewares(string $className, string $methodName): array
    {
        $middlewares = self::$handlerMiddlewares[$className][$methodName] ?? [];
        if (!empty($middlewares)) {
            return $middlewares;
        }

        return self::$middlewares[$className]['class'] ?? [];
    }
}
