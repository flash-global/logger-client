<?php

namespace Fei\Service\Logger\Client;

use Fei\ApiClient\AbstractApiClient;
use Fei\ApiClient\ApiRequestOption;
use Fei\ApiClient\RequestDescriptor;
use Fei\Service\Logger\Client\Builder\SearchBuilder;
use Fei\Service\Logger\Client\Exception\LoggerException;
use Fei\Service\Logger\Entity\Notification;
use Fei\Service\Logger\Validator\NotificationValidator;

class Logger extends AbstractApiClient implements LoggerInterface
{
    const OPTION_BASEURL = 'baseUrl';
    const OPTION_FILTER = 'filterLevel';
    const OPTION_BACKTRACE = 'includeBacktrace';
    const OPTION_LOGFILE = 'exceptionLogFile';
    const OPTION_APIKEY = 'apiKey';

    /**
     * @var int
     */
    protected $filterLevel = Notification::LVL_ERROR;

    /**
     * @var string
     */
    protected $exceptionLogFile = '/tmp/logger.log';

    /**
     * @var bool
     */
    protected $includeBacktrace = true;

    /** @var string */
    protected $apiKey = '';

    /**
     * @var mixed
     */
    protected $previousErrorHandler;

    /**
     * @param array|SearchBuilder $criteria
     *
     * @return bool|\Fei\ApiClient\ResponseDescriptor
     */
    public function retrieve($criteria)
    {
        if ($criteria instanceof SearchBuilder) {
            $criteria = $criteria->getParams();
        }

        if (!is_array($criteria)) {
            throw new LoggerException('$criteria has to be of type array of SearchBuilder');
        }

        try {
            $this->registerErrorHandler();

            $request = new RequestDescriptor();

            $request->setUrl($this->buildUrl('/api/notifications?criteria=' . urlencode(json_encode($criteria))));
            $request->setMethod('GET');

            $return = $this->send($request, ApiRequestOption::NO_RESPONSE);

            $this->restoreErrorHandler();

            return $return;
        } catch (\Exception $e) {
            $this->writeToExceptionLogFile($e->getMessage());
            $this->restoreErrorHandler();
        }
    }
    /**
     * @param string|Notification $message
     * @param array               $params
     *
     * @return bool|\Fei\ApiClient\ResponseDescriptor
     */
    public function notify($message, array $params = array())
    {
        try {
            $this->registerErrorHandler();

            if (is_string($message)) {
                $notification = new Notification();
                $notification->setMessage($message)
                    ->setLevel(Notification::LVL_INFO)
                    ->setCategory(Notification::BUSINESS);
            } else {
                $notification = $message;
            }

            $this->prepareNotification($notification, $params);

            if ($notification->getLevel() < $this->filterLevel) {
                $this->restoreErrorHandler();
                return false;
            }

            if ($this->includeBacktrace) {
                $notification->setBackTrace($this->getBackTrace());
            }

            $validator = new NotificationValidator();
            $validator->validate($notification);

            $errors = $validator->getErrors();
            if (!empty($errors)) {
                throw new \LogicException(
                    sprintf('Notification validator errors: %s', $validator->getErrorsAsString())
                );
            }

            $serialized = @json_encode($notification->toArray());
            if (!$serialized) {
                $this->restoreErrorHandler();
                return false;
            }

            $request = new RequestDescriptor();
            $request->addBodyParam('notification', $serialized);


            if ($this->apiKey) {
                $request->addHeader('Authorization', $this->getApiKey());
            }

            $request->setUrl($this->buildUrl('/api/notifications'));
            $request->setMethod('POST');

            $return = $this->send($request, ApiRequestOption::NO_RESPONSE);

            $this->restoreErrorHandler();

            return $return;
        } catch (\Exception $e) {
            $this->writeToExceptionLogFile($e->getMessage());
            $this->restoreErrorHandler();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        try {
            $this->registerErrorHandler();

            parent::commit();

            $this->restoreErrorHandler();
        } catch (\Exception $e) {
            $this->writeToExceptionLogFile($e->getMessage());
            $this->restoreErrorHandler();
        }
    }

    /**
     * @return string
     */
    public function getApiKey() {
        return $this->apiKey;
    }

    /**
     * @param $apiKey
     *
     * @return Logger
     */
    public function setApiKey($apiKey) {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * @return int
     */
    public function getFilterLevel()
    {
        return $this->filterLevel;
    }

    /**
     * @param int $filterLevel
     *
     * @return $this
     */
    public function setFilterLevel($filterLevel)
    {
        $this->filterLevel = $filterLevel;

        return $this;
    }

    /**
     * @param Notification $notification
     * @param array        $params
     *
     * @return Notification
     */
    protected function prepareNotification(Notification $notification, $params = array())
    {
        $data = array_filter($notification->toArray());
        // Prevent from duplicating context items
        unset($data['context']);

        $params += array('origin' => php_sapi_name() == 'cli' ? 'cli' : 'http');
        $params += array('reported_at' => new \DateTime());
        $params += array('server' => $this->getServerName());

        $data += $params;

        $notification->hydrate($data);

        return $notification;
    }

    /**
     * Add a error handler
     */
    protected function registerErrorHandler()
    {
        $instance = $this;
        $this->previousErrorHandler = set_error_handler(
            function ($errno, $errstr, $errfile, $errline) use ($instance) {
                $message = sprintf('%d: %s - File: %s - Line: %d', $errno, $errstr, $errfile, $errline);
                throw new \Exception($message, $errno);
            }
        );
    }

    /**
     * Restore previous error handler
     */
    protected function restoreErrorHandler()
    {
        restore_error_handler();
    }

    /**
     * @return string
     */
    protected function getServerName()
    {
        $uname = posix_uname();

        return $uname['nodename'];
    }

    /**
     * @return array
     */
    protected function getBackTrace()
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT);

        $methodExcluded = [
            'Fei\Service\Logger\Client\Logger->getBackTrace',
            'Fei\Service\Logger\Client\Logger->notify'
        ];

        $sanitized = array();

        foreach ($backtrace as $key => $trace) {
            if (isset($trace['file']) && $trace['line']) {
                $sanitized[$key]['file'] = $trace['file'] . ':' . $trace['line'];
            }

            if (isset($trace['class'])) {
                $sanitized[$key]['method'] = $trace['class'] . $trace['type'] . $trace['function'];
                if (in_array($sanitized[$key]['method'], $methodExcluded)) {
                    unset($sanitized[$key]);
                    continue;
                }
            } elseif (isset($trace['function'])) {
                $sanitized[$key]['function'] = $trace['function'];
            }

            if (!empty($trace['args'])) {
                foreach ($trace['args'] as $arg) {
                    if (is_scalar($arg)) {
                        $sanitized[$key]['args'][] = sprintf('(%s) %s', gettype($arg), (string) $arg);
                    } elseif (is_array($arg)) {
                        $sanitized[$key]['args'][] = sprintf('array(%d)', count($arg));
                    } elseif (is_object($arg)) {
                        $sanitized[$key]['args'][] = sprintf('Instance of %s', get_class($arg));
                    } elseif (is_resource($arg)) {
                        $sanitized[$key]['args'][] = sprintf('(resource) %s', get_resource_type($arg));
                    }
                }
            }
        }

        return array_values($sanitized);
    }

    /**
     * Write to exception log file
     *
     * @param string $message
     */
    protected function writeToExceptionLogFile($message)
    {
        if (is_writable($this->exceptionLogFile) || is_writable(dirname($this->exceptionLogFile))) {
            $message = sprintf('[%s] %s' . PHP_EOL, (new \DateTime())->format(\DateTime::ISO8601), $message);
            @file_put_contents($this->exceptionLogFile, $message, FILE_APPEND);
        }
    }
}
