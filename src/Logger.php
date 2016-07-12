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
use Fei\Service\Logger\Entity\Notification;

class Logger extends AbstractApiClient implements LoggerInterface
{
    /** @var  int */
    protected $filterLevel;

    /** @var  string */
    protected $exceptionLogFile;

    /** @var bool  */
    protected $haveBackTrace = true;

    const PARAMETER_BASEURL = 'baseUrl';
    const PARAMETER_FILTER = 'filter';
    const PARAMETER_BACKTRACE = 'includedBackTrace';

    /**
     * Logger constructor.
     *
     * @param array $options
     *
     */
    public function __construct(array $options = array())
    {
        $this->exceptionLogFile = '/tmp/logger.log';
        $this->filterLevel = isset($options[self::PARAMETER_FILTER]) ? $options[self::PARAMETER_FILTER] : Notification::DEBUG;
        $this->haveBackTrace = isset($options[self::PARAMETER_BACKTRACE]) ? $options[self::PARAMETER_BACKTRACE] : true;
        if(isset($options[self::PARAMETER_BASEURL]) ) {
            $this->setBaseUrl($options[self::PARAMETER_BASEURL]);
        }
    }

    /**
     * @param       $notif
     * @param array $params
     *
     * @return $this|\Fei\ApiClient\ResponseDescriptor
     */
    public function notify($notif, array $params = [])
    {
        try {
            if (is_string($notif)) {
                $notification = new Notification();
                $notification->setMessage($notif)
                    ->setLevel(Notification::INFO)
                    ->setCategory(Notification::BUSINESS);
            }else{
                $notification = $notif;
            }

            $this->prepareNotification($notification, $params);

            $request = new RequestDescriptor();
            $request->addBodyParam('message', $notification->getMessage());
            $request->addBodyParam('context', json_encode($notification->getContext()));
            $request->addBodyParam('origin', 'http');
            $request->addBodyParam('level', (int)$notification->getLevel());
            $request->addBodyParam('namespace', $notification->getNamespace());
            $request->addBodyParam('server', $this->getServerName());
            $request->addBodyParam('user', $notification->getUser());
            $request->addBodyParam('command', $notification->getCommand());
            $request->addBodyParam('env', $notification->getEnv());
            $request->addBodyParam('category', $notification->getCategory());

            if ($this->haveBackTrace) {
                $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3);
                unset($backtrace[0]);
                $request->addBodyParam('backtrace', json_encode($backtrace));
            }

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

    /**
     * @return string
     */
    protected function getServerName()
    {
        $uname = posix_uname();

        return $uname['nodename'];
    }
    

    protected function prepareNotification(Notification $notification, $params = null)
    {
        $notification->hydrate($params);

        return $notification;
    }
}