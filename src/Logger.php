<?php

namespace Fei\Service\Logger\Client;

use Fei\ApiClient\AbstractApiClient;
use Fei\ApiClient\ApiRequestOption;
use Fei\ApiClient\RequestDescriptor;
use Fei\Service\Logger\Entity\Notification;
use Fei\Service\Logger\Validator\NotificationValidator;

class Logger extends AbstractApiClient implements LoggerInterface
{
    const PARAMETER_BASEURL = 'baseUrl';

    const PARAMETER_FILTER = 'filter';

    const PARAMETER_BACKTRACE = 'includedBackTrace';

    /** @var  int */
    protected $filterLevel;

    /** @var  string */
    protected $exceptionLogFile;

    /** @var bool */
    protected $includeBacktrace = true;

    /**
     * @var mixed
     */
    protected $previousErrorHandler;

    /**
     * Logger constructor.
     *
     * @param array $options
     *
     */
    public function __construct(array $options = array())
    {
        $this->exceptionLogFile = '/tmp/logger.log';
        $this->filterLevel = isset($options[self::PARAMETER_FILTER]) ? $options[self::PARAMETER_FILTER] : Notification::LVL_DEBUG;
        $this->includeBacktrace = isset($options[self::PARAMETER_BACKTRACE]) ? $options[self::PARAMETER_BACKTRACE] : true;
        if (isset($options[self::PARAMETER_BASEURL])) {
            $this->setBaseUrl($options[self::PARAMETER_BASEURL]);
        }
    }

    /**
     * @param       $message
     * @param array $params
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
                return false;
            }

            if ($this->includeBacktrace) {
                $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT);
                unset($backtrace[0]);
                $notification->setBackTrace($backtrace);
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
            if (is_null($serialized)) {
                return false;
            }

            $request = new RequestDescriptor();
            $request->addBodyParam('notification', $serialized);

            $request->setUrl($this->buildUrl('/api/notifications'));
            $request->setMethod('POST');

            $return = $this->send($request, ApiRequestOption::NO_RESPONSE);

            $this->restoreErrorHandler();

            return $return;
        } catch (\Exception $e) {
            @file_put_contents($this->exceptionLogFile, $e, FILE_APPEND);
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
            @file_put_contents($this->exceptionLogFile, $e, FILE_APPEND);
            $this->restoreErrorHandler();
        }
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
        $this->previousErrorHandler = set_error_handler(function($errno , $errstr, $errfile, $errline) use ($instance) {
            $message = sprintf('%d: %s - File: %s - Line: %d', $errno, $errstr, $errfile, $errline);
            throw new \Exception($message, $errno);
        });
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
}
