<?php
namespace Fei\Service\Logger\Client\Builder\Fields;

interface FieldInterface
{
    /**
     * Build the filter
     *
     * @param $value
     * @param null $operator
     *
     * @return mixed
     */
    public function build($value, $operator = null);
}
