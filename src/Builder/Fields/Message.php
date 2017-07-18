<?php
namespace Fei\Service\Logger\Client\Builder\Fields;

use Fei\Service\Logger\Client\Builder\OperatorBuilder;

class Message extends OperatorBuilder
{
    public function build($value, $operator = null)
    {
        $search = $this->builder->getParams();
        $search['notification_message'] = $value;
        $search['notification_operator'] = (isset($operator)) ? $operator : '=';

        $this->builder->setParams($search);
    }
}
