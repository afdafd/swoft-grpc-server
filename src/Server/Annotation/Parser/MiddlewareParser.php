<?php declare(strict_types=1);

namespace App\Grpc\Server\Annotation\Parser;

use Swoft\Grpc\Server\Annotation\Mapping\Middleware;
use Swoft\Grpc\Server\Middleware\MiddlewareRegister;
use Swoft\Annotation\Annotation\Mapping\AnnotationParser;
use Swoft\Annotation\Annotation\Parser\Parser;


/**
 * Class MiddlewareParser
 * @package App\Grpc\Server\Annotation\Parser
 *
 * @AnnotationParser(Middleware::class)
 */
class MiddlewareParser extends Parser
{
    /**
     * @param int        $type
     * @param Middleware $annotationObject
     *
     * @return array
     */
    public function parse(int $type, $annotationObject): array
    {
        $name =$annotationObject->getName();
        if ($type === self::TYPE_CLASS) {
            MiddlewareRegister::registerByClassName($name, $this->className);
            return [];
        }

        if ($type === self::TYPE_METHOD) {
            MiddlewareRegister::registerByMethodName($name, $this->className, $this->methodName);
            return [];
        }

        return [];
    }
}
