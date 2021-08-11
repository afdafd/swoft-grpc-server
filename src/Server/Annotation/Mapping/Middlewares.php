<?php declare(strict_types=1);

namespace Swoft\Grpc\Server\Annotation\Mapping;

use Doctrine\Common\Annotations\Annotation\Attribute;
use Doctrine\Common\Annotations\Annotation\Attributes;
use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;


/**
 * Class Middlewares
 * @package Swoft\Grpc\Server\Annotation\Mapping
 *
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 * @Attributes({
 *     @Attribute("name", type="array"),
 * })
 */
class Middlewares
{
    /**
     * Middlewares
     *
     * @var Middleware[]
     *
     * @Required()
     */
    private $middlewares = [];

    /**
     * Middlewares constructor.
     *
     * @param $values
     */
    public function __construct($values)
    {
        if (isset($values['value'])) {
            $this->middlewares = $values['value'];
        }
        if (isset($values['middlewares'])) {
            $this->middlewares = $values['middlewares'];
        }
    }

    /**
     * @return Middleware[]
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}
