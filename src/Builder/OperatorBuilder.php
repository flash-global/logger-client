<?php
namespace Fei\Service\Logger\Client\Builder;

abstract class OperatorBuilder extends AbstractBuilder
{
    /**
     * Set the like operator for the current filter
     *
     * @param $value
     * @return $this
     */
    public function like($value)
    {
        $this->build("%$value%", 'like');

        return $this;
    }

    /**
     * Set the like operator and begins with for the current filter
     *
     * @param $value
     * @return $this
     */
    public function beginsWith($value)
    {
        $this->build("$value%", 'like');

        return $this;
    }

    /**
     * Set the like operator and ends with for the current filter
     *
     * @param $value
     * @return $this
     */
    public function endsWith($value)
    {
        $this->build("%$value", 'like');

        return $this;
    }
}
