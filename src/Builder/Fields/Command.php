<?php
namespace Fei\Service\Logger\Client\Builder\Fields;

use Fei\Service\Logger\Client\Builder\OperatorBuilder;

class Command extends OperatorBuilder
{
    public function build($value, $operator = null)
    {
        $search = $this->builder->getParams();
        $search['notification_command'] = $value;
        $search['notification_command_operator'] = (isset($operator)) ? $operator : '=';

        $this->builder->setParams($search);
    }
}
