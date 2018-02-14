<?php

namespace Fei\Service\Logger\Client\Psr;

/**
 * Class ContextExtractor
 *
 * @package Fei\Service\Logger\Client\Psr
 */
class ContextExtractor
{
    /**
     * @var array
     */
    protected $fields = [
        'flag',
        'namespace',
        'user',
        'server',
        'command',
        'origin',
        'category',
        'env'
    ];

    /**
     * @param array $context
     *
     * @return array
     */
    public function extract(array $context)
    {
        $params = [];

        foreach ($this->fields as $field) {
            if (isset($context[$field])) {
                $params[$field] = $context[$field];
                unset($context[$field]);
            }
        }

        if (!empty($context)) {
            $params['context'] = $context;
        }

        return $params;
    }
}
