<?php
namespace Fei\Service\Logger\Client\Builder\Fields;

use Fei\Service\Logger\Client\Builder\OperatorBuilder;

class ReportedAt extends OperatorBuilder
{
    public function build($value, $operator = '=')
    {
        $search = $this->builder->getParams();
        $search['notification_reportedAt'] = $value;
        $search['notification_reportedAt_operator'] = $operator;

        $this->builder->setParams($search);
    }

    public function from($value)
    {
        $search = $this->builder->getParams();
        $search['notification_reportedAt'] = $value;

        $this->builder->setParams($search);

        return $this;
    }

    public function till($value)
    {
        $search = $this->builder->getParams();
        $search['notification_reportedAt_till'] = $value;

        $this->builder->setParams($search);

        return $this;
    }
}
