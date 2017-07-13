<?php
namespace Fei\Service\Logger\Client\Builder;

use Fei\Service\Logger\Client\Builder\Fields\FieldInterface;

abstract class AbstractBuilder implements FieldInterface
{
    protected $builder;
    protected $value;
    protected $in_cache;

    public function __construct(SearchBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * Set the equal operator for the current filter
     *
     * @param $value
     * @return $this
     */
    public function equal($value)
    {
        $this->build($value, '=');

        return $this;
    }

    /**
     * Get InCache
     *
     * @return mixed
     */
    public function getInCache()
    {
        return $this->in_cache;
    }

    /**
     * Set InCache
     *
     * @param mixed $in_cache
     *
     * @return $this
     */
    public function setInCache($in_cache)
    {
        $this->in_cache = $in_cache;
        return $this;
    }
}
