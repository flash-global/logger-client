<?php
namespace Fei\Service\Logger\Client\Builder\Fields;

use Fei\Service\Logger\Client\Builder\AbstractBuilder;

class Category extends AbstractBuilder
{
    public function build($value, $operator = '=')
    {
        $search = $this->builder->getParams();
        $search['category'] = $value;

        $this->builder->setParams($search);
    }
}
