<?php
/**
 * Created by PhpStorm.
 * User: Neofox
 * Date: 21/06/2016
 * Time: 13:42
 */

namespace Pricer\Logger\Client;


use Fei\ApiClient\AbstractApiClient;
use Fei\ApiClient\ApiRequestOption;
use Fei\ApiClient\RequestDescriptor;

class Logger extends AbstractApiClient implements LoggerInterface
{
    /** @var  int */
    protected $filterLevel;

    /** @var  string */
    protected $exceptionLogFile;

    /**
     * Logger constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->exceptionLogFile = '/tmp/logger.log';
        $this->filterLevel = !empty($options['filter']) ? $options['filter'] : Notification::LEVEL_DEBUG;
        $loggerUrl = !empty($options['baseUrl']) ? $options['baseUrl'] : $this->getServerUrl();
        $this->setBaseUrl($loggerUrl);
    }

    public function getServerUrl()
    {
        switch (getenv('APP_ENV')) {
            case 'prod':
                //$url = '';
                //break;
            case 'dev':
                $url = 'http://logger.test.flash-global.net';
                break;
            case 'test':
                $url = 'http://192.168.5.110:8080';
                break;
            default:
                $url = 'http://logger.test.flash-global.net';
                break;
        }

        return $url;

    }

    public function notify($notification, array $params = [])
    {
        try {
            if (is_string($notification)) {
                $notification = new Notification($notification, Notification::LEVEL_INFO);
            }

            $notification->hydrate($params);

            $request = new RequestDescriptor();
            $request->addBodyParam('message', $notification->getMessage());
            $request->addBodyParam('context', $notification->getContext());
            $request->addBodyParam('origin', 'http');
            $request->addBodyParam('level', (int)$notification->getLevel());
            $request->addBodyParam('location', $notification->getLocation());
            $request->addBodyParam('server', $this->getServerName());
            $request->addBodyParam('user', $params['login']);
            $request->addBodyParam('command', $params['command']);
            $request->addBodyParam('env', $params['env']);
            $request->addBodyParam('category', $notification->getCategory());
            $request->addBodyParam('backtrace', json_encode(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3)));

            $request->setUrl($this->buildUrl('/api/notifications'));
            $request->setMethod('POST');

            if ($notification->getLevel() >= $this->filterLevel) {
                return $this->send($request, ApiRequestOption::NO_RESPONSE);
            }

        } catch (\Exception $e) {
            file_put_contents($this->exceptionLogFile, $e, FILE_APPEND);
        }

        return $this;
    }

    public function __destruct()
    {
        $this->flush();
    }

    /**
     * @return array|bool
     * @throws \Exception
     */
    public function flush()
    {
        return $this->commit();
    }

    /**
     * @return LoggerInterface
     */
    public function hold()
    {
        return $this->begin();
    }

}