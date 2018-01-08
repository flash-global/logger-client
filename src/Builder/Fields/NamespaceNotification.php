<?php
namespace Fei\Service\Logger\Client\Builder\Fields;

use Fei\Service\Logger\Client\Builder\AbstractBuilder;

class NamespaceNotification extends AbstractBuilder
{
    public function build($value, $operator = '=')
    {
        $search = $this->builder->getParams();
        $search['namespace'] = $value;

        $this->builder->setParams($search);
    }
}
