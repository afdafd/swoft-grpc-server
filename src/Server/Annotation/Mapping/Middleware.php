<?php declare(strict_types=1);

namespace Swoft\Grpc\Server\Annotation\Mapping;

use Doctrine\Common\Annotations\Annotation\Attribute;
use Doctrine\Common\Annotations\Annotation\Attributes;
use Doctrine\Common\Annotations\Annotation\Required;
use Doctrine\Common\Annotations\Annotation\Target;


/**
 * Class Middleware
 * @package Swoft\Grpc\Server\Annotation\Mapping
 *
 * @Annotation
 * @Target({"CLASS", "METHOD", "ANNOTATION"})
 * @Attributes({
 *     @Attribute("name", type="string"),
 * })
 */
class Middleware
{
    /**
     * @var string
     *
     * @Required()
     */
    private $name = '';

    /**
     * Middleware constructor.
     *
     * @param array $values
     */
    public function __construct(array $values)
    {
        if (isset($values['value'])) {
            $this->name = $values['value'];
        }
        if (isset($values['name'])) {
            $this->name = $values['name'];
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
