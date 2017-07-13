<?php
namespace Fei\Service\Logger\Client\Builder;

use Fei\Service\Logger\Client\Builder\Fields\Category;
use Fei\Service\Logger\Client\Builder\Fields\Command;
use Fei\Service\Logger\Client\Builder\Fields\Context;
use Fei\Service\Logger\Client\Builder\Fields\Env;
use Fei\Service\Logger\Client\Builder\Fields\Level;
use Fei\Service\Logger\Client\Builder\Fields\Message;
use Fei\Service\Logger\Client\Builder\Fields\NamespaceNotification;
use Fei\Service\Logger\Client\Builder\Fields\ReportedAt;
use Fei\Service\Logger\Client\Builder\Fields\Server;
use Fei\Service\Logger\Client\Exception\LoggerException;

class SearchBuilder
{
    protected $params = [];

    /**
     * Search on the namespace
     *
     * @return NamespaceNotification
     */
    public function namespaceNotification()
    {
        return new NamespaceNotification($this);
    }

    /**
     * Search on the level
     *
     * @return Level
     */
    public function level()
    {
        return new Level($this);
    }

    /**
     * Search on the env
     *
     * @return Env
     */
    public function env()
    {
        return new Env($this);
    }

    /**
     * Search on the category
     *
     * @return Category
     */
    public function category()
    {
        return new Category($this);
    }

    /**
     * Search on the message
     *
     * @return Message
     */
    public function message()
    {
        return new Message($this);
    }

    /**
     * Search on the reportedAt
     *
     * @return ReportedAt
     */
    public function reportedAt()
    {
        return new ReportedAt($this);
    }

    /**
     * Search on the command
     *
     * @return Command
     */
    public function command()
    {
        return new Command($this);
    }

    /**
     * Search on the server
     *
     * @return Server
     */
    public function server()
    {
        return new Server($this);
    }

    /**
     * Set the condition type for the contexts
     *
     * @param string $type
     *
     * @return $this
     */
    public function contextCondition($type = 'AND')
    {
        $type = strtoupper($type);

        if (!in_array($type, ['AND', 'OR'])) {
            throw new LoggerException('Type has to be either "AND" or "OR"!');
        }

        $params = $this->getParams();
        $params['context_condition'] = $type;

        $this->setParams($params);

        return $this;
    }

    /**
     * Add a filter the the contexts
     *
     * @return Context
     */
    public function context()
    {
        return new Context($this);
    }

    /**
     * Get Params
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set Params
     *
     * @param array $params
     *
     * @return $this
     */
    public function setParams($params)
    {
        $this->params = $params;

        return $this;
    }
    
    public function __call($name, $arguments)
    {
        $class = 'Fei\Service\Logger\Client\Builder\Fields\\' . ucfirst($this->toCamelCase($name));

        if (class_exists($class)) {
            return new $class($this);
        } else {
            throw new \Exception("Cannot load " . $name . ' filter!');
        }
    }

    /**
     * @param $offset
     *
     * @return string
     */
    public function toCamelCase($offset)
    {
        $parts = explode('_', $offset);
        array_walk($parts, function (&$offset) {
            $offset = ucfirst($offset);
        });

        return implode('', $parts);
    }
}
