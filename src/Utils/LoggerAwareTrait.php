<?php
/**
 * LoggerAwareTrait.php
 *
 * @date        8/08/17
 * @file        LoggerAwareTrait.php
 */

namespace Fei\Service\Logger\Client\Utils;

use Fei\Service\Logger\Client\Logger;

/**
 * LoggerAwareTrait
 */
trait LoggerAwareTrait
{
    /**
     * @var Logger
     */
    protected $loggerClient;

    /**
     * @return Logger
     */
    public function getLoggerClient()
    {
        return $this->loggerClient;
    }

    /**
     * @param Logger $loggerClient
     *
     * @return LoggerAwareTrait
     */
    public function setLoggerClient($loggerClient)
    {
        $this->loggerClient = $loggerClient;

        return $this;
    }
}
