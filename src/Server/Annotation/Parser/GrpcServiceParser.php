<?php declare(strict_types=1);

namespace Swoft\Grpc\Server\Annotation\Parser;

use ReflectionException;
use Swoft\Annotation\Annotation\Mapping\AnnotationParser;
use Swoft\Annotation\Annotation\Parser\Parser;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Grpc\Server\Annotation\Mapping\GrpcService;
use Swoft\Grpc\Server\Router\RouteRegister;

/**
 * Class GrpcServiceParser
 * @package Swoft\Grpc\Server\Annotation\Parser
 *
 * @AnnotationParser(annotation=GrpcService::class)
 */
class GrpcServiceParser extends Parser
{
    /**
     * @param int $type
     * @param object $annotationObject
     * @return array
     * @throws ReflectionException
     */
    public function parse(int $type, $annotationObject): array
    {
        $reflectionClass = new \ReflectionClass($this->className);

        foreach ($reflectionClass->getMethods() as $name => $value) {
            $prefix = $annotationObject->getPrefix() . ucfirst($value->getName());
            RouteRegister::register($prefix, $this->className);
        }

        return [$this->className, $this->className, Bean::SINGLETON, ''];
    }
}
