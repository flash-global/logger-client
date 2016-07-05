<?php
/**
 * Created by PhpStorm.
 * User: Neofox
 * Date: 21/06/2016
 * Time: 13:42
 */

namespace Pricer\Logger\Client;


class Notification
{

    // level
    const LEVEL_DEBUG   = 1;
    const LEVEL_INFO    = 2;
    const LEVEL_WARNING = 4;
    const LEVEL_ERROR   = 8;
    const LEVEL_PANIC   = 16;

    // category
    const CATEGORY_SECURITY    = 1;
    const CATEGORY_PERFORMANCE = 2;
    const CATEGORY_BUSINESS    = 4;


    /** @var  string */
    protected $message;

    /** @var  int */
    protected $level;

    /** @var  array */
    protected $context;

    /** @var  string */
    protected $location;

    /** @var  int */
    protected $category;

    /** @var  LoggerInterface */
    protected $logger;

    /**
     * Notification constructor.
     *
     * @param string $message
     * @param int    $level
     */
    public function __construct($message, $level)
    {
        $this->message = $message;
        $this->level = $level;
    }

    /**
     * @return int
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param int $category
     *
     * @return Notification
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param array $context
     *
     * @return Notification
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param string $location
     *
     * @return Notification
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     *
     * @return Notification
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param int $level
     *
     * @return Notification
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    public function hydrate(array $params)
    {
        foreach ($params as $key => $param) {
            $setter = 'set'. ucfirst($key);

            if(method_exists($this, $setter)) $this->$setter($param);
        }

        return $this;
    }

}