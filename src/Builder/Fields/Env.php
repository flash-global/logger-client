<?php
namespace Fei\Service\Logger\Client\Builder\Fields;

use Fei\Service\Logger\Client\Builder\AbstractBuilder;

class Env extends AbstractBuilder
{
    public function build($value, $operator = '=')
    {
        $search = $this->builder->getParams();
        $search['env'] = $value;

        $this->builder->setParams($search);
    }
}
