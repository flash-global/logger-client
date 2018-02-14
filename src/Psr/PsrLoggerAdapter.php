<?php

namespace Fei\Service\Logger\Client\Psr;

use Fei\Service\Logger\Client\LoggerInterface as ClientLoggerInterface;
use Fei\Service\Logger\Entity\Notification;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Class PsrAdapter
 *
 * @package Fei\Service\Logger\Client
 */
class PsrLoggerAdapter implements LoggerInterface
{
    /**
     * @var array
     */
    protected $levelMapping = [
        LogLevel::EMERGENCY => Notification::LVL_PANIC,
        LogLevel::ALERT => Notification::LVL_PANIC,
        LogLevel::CRITICAL => Notification::LVL_PANIC,
        LogLevel::ERROR => Notification::LVL_ERROR,
        LogLevel::WARNING => Notification::LVL_WARNING,
        LogLevel::NOTICE => Notification::LVL_INFO,
        LogLevel::INFO => Notification::LVL_INFO,
        LogLevel::DEBUG => Notification::LVL_DEBUG
    ];

    /**
     * @var ClientLoggerInterface
     */
    protected $client;

    /**
     * @var ContextExtractor
     */
    protected $extractor;

    /**
     * PsrAdapter constructor.
     *
     * @param \Fei\Service\Logger\Client\LoggerInterface $client
     */
    public function __construct(ClientLoggerInterface $client)
    {
        $this->setClient($client);
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function emergency($message, array $context = array())
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function alert($message, array $context = array())
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function critical($message, array $context = array())
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function error($message, array $context = array())
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function warning($message, array $context = array())
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function notice($message, array $context = array())
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function info($message, array $context = array())
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function debug($message, array $context = array())
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed  $level
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    public function log($level, $message, array $context = array())
    {
        $this->getClient()->notify(
            $message,
            array_merge(['level' => $this->levelMapping[$level]], $this->getExtractor()->extract($context))
        );
    }

    /**
     * Get Client
     *
     * @return \Fei\Service\Logger\Client\LoggerInterface
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set Client
     *
     * @param \Fei\Service\Logger\Client\LoggerInterface $client
     *
     * @return $this
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get Extractor
     *
     * @return ContextExtractor
     */
    public function getExtractor()
    {
        if (is_null($this->extractor)) {
            $this->setExtractor(new ContextExtractor());
        }

        return $this->extractor;
    }

    /**
     * Set Extractor
     *
     * @param ContextExtractor $extractor
     *
     * @return $this
     */
    public function setExtractor(ContextExtractor $extractor)
    {
        $this->extractor = $extractor;

        return $this;
    }
}
