<?php
/**
 * Created by PhpStorm.
 * User: Neofox
 * Date: 21/06/2016
 * Time: 13:41
 */

namespace Pricer\Logger\Client;


use Pricer\WebClient\AbstractClient;
use Pricer\WebClient\Transport\TransportInterface;

abstract class AbstractLogger extends AbstractClient implements LoggerInterface
{

    /** @var array */
    protected $requestStack;

    /** @var string */
    protected $loggerUrl;

    /** @var bool */
    protected $hold = false;

    /**
     * @return array
     */
    public function getRequestStack()
    {
        return $this->requestStack;
    }

    /**
     * @param array $requestStack
     *
     * @return AbstractLogger
     */
    public function setRequestStack($requestStack)
    {
        $this->requestStack = $requestStack;

        return $this;
    }

    /**
     * @return string
     */
    public function getLoggerUrl()
    {
        return $this->loggerUrl;
    }

    /**
     * @param string $loggerUrl
     *
     * @return AbstractLogger
     */
    public function setLoggerUrl($loggerUrl)
    {
        $this->loggerUrl = $loggerUrl;

        return $this;
    }


    /**
     * @return $this
     */
    public function hold()
    {
        $this->setHold(true);

        return $this;
    }

    /**
     * @return boolean
     */
    public function isOnHold()
    {
        return $this->hold;
    }

    /**
     * @param boolean $hold
     *
     * @return AbstractLogger
     */
    public function setHold($hold)
    {
        $this->hold = $hold;

        return $this;
    }

}