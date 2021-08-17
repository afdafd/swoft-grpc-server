<?php declare(strict_types=1);

namespace Hzwz\Grpc\Server\Annotation\Mapping;

use Doctrine\Common\Annotations\Annotation\Attribute;
use Doctrine\Common\Annotations\Annotation\Attributes;
use Doctrine\Common\Annotations\Annotation\Target;
use Hzwz\Grpc\Server\Exception\GrpcServerException;

/**
 * Class Service
 * @package Hzwz\Grpc\Server\Annotation\Mapping
 *
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *     @Attribute("prefix", type="string"),
 * })
 */
class GrpcService
{
    /**
     * @var string
     */
    protected $prefix = '';

  /**
   * @var string
   */
    protected $method = 'POST';

    /**
     * Service constructor.
     *
     * @param array $values
     */
    public function __construct(array $values)
    {
        if (isset($values['value'])) {
            $this->prefix = $values['value'];
        }
        if (isset($values['prefix'])) {
            $this->prefix = $values['prefix'];
        }

        if (empty($this->prefix)) {
            throw new GrpcServerException("prefix is empty");
        }
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        $_prefix = '/';
        foreach(explode('/', $this->prefix) as $pre) {
            $_prefix .= $pre . '.';
        }

        return rtrim($_prefix, '.') . '/';
    }

  /**
   * @return string
   */
    public function getMethod(): string
    {
      return $this->method;
    }
}
