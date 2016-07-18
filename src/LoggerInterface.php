<?php

namespace Fei\Service\Logger\Client;


interface LoggerInterface
{
    /**
     * @param       $message
     * @param array $params
     *
     * @return mixed|bool
     */
    public function notify($message, array $params);

}
