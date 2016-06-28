<?php
/**
 * Created by PhpStorm.
 * User: Neofox
 * Date: 21/06/2016
 * Time: 13:42
 */

namespace Pricer\Logger\Client;


interface LoggerInterface
{
    /**
     * @param       $message
     * @param       $level
     * @param array $params
     *
     * @return mixed|bool
     */
    public function log($message, $level, array $params);

    /**
     * @return LoggerInterface
     */
    public function hold();

    /**
     * @return array|bool
     */
    public function flush();

}