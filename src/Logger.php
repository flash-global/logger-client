<?php
    
    namespace Fei\Service\Logger\Client;
    
    use Fei\ApiClient\AbstractApiClient;
    use Fei\ApiClient\ApiRequestOption;
    use Fei\ApiClient\RequestDescriptor;
    use Fei\Service\Logger\Entity\ContextTransformer;
    use Fei\Service\Logger\Entity\Notification;
    
    class Logger extends AbstractApiClient implements LoggerInterface
    {
        const PARAMETER_BASEURL   = 'baseUrl';
        
        const PARAMETER_FILTER    = 'filter';
        
        const PARAMETER_BACKTRACE = 'includedBackTrace';
        
        /** @var  int */
        protected $filterLevel;

        /** @var  string */
        protected $exceptionLogFile;

        /** @var bool */
        protected $haveBackTrace = true;
        
        /**
         * Logger constructor.
         *
         * @param array $options
         *
         */
        public function __construct(array $options = array())
        {
            $this->exceptionLogFile = '/tmp/logger.log';
            $this->filterLevel      = isset($options[self::PARAMETER_FILTER]) ? $options[self::PARAMETER_FILTER] : Notification::LVL_DEBUG;
            $this->haveBackTrace    = isset($options[self::PARAMETER_BACKTRACE]) ? $options[self::PARAMETER_BACKTRACE] : true;
            if (isset($options[self::PARAMETER_BASEURL]))
            {
                $this->setBaseUrl($options[self::PARAMETER_BASEURL]);
            }
        }
        
        /**
         * @param       $message
         * @param array $params
         *
         * @return $this|\Fei\ApiClient\ResponseDescriptor
         */
        public function notify($message, array $params = [])
        {
            try
            {
                if (is_string($message))
                {
                    $notification = new Notification();
                    $notification->setMessage($message)
                                 ->setLevel(Notification::LVL_INFO)
                                 ->setCategory(Notification::BUSINESS)
                    ;
                }
                else
                {
                    $notification = $message;
                }
                
                $this->prepareNotification($notification, $params);
                
                $context = array();
                $contextTransformer = new ContextTransformer();
                foreach ($notification->getContext() as $contextItem)
                {
                    $context[$contextItem->getKey()] = $contextItem->getValue();
                }
                
                $request = new RequestDescriptor();
                $request->addBodyParam('message', $notification->getMessage());
                $request->addBodyParam('context', json_encode($context));
                $request->addBodyParam('origin', 'http');
                $request->addBodyParam('level', (int) $notification->getLevel());
                $request->addBodyParam('namespace', $notification->getNamespace());
                $request->addBodyParam('server', $this->getServerName());
                $request->addBodyParam('user', $notification->getUser());
                $request->addBodyParam('command', $notification->getCommand());
                $request->addBodyParam('env', $notification->getEnv());
                $request->addBodyParam('category', $notification->getCategory());
                
                if ($this->haveBackTrace)
                {
                    $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3);
                    unset($backtrace[0]);
                    $request->addBodyParam('backtrace', json_encode($backtrace));
                }
                
                $request->setUrl($this->buildUrl('/api/notifications'));
                $request->setMethod('POST');
                
                if ($notification->getLevel() >= $this->filterLevel)
                {
                    return $this->send($request, ApiRequestOption::NO_RESPONSE);
                }
                
            } catch (\Exception $e)
            {
                file_put_contents($this->exceptionLogFile, $e, FILE_APPEND);
            }
            
            return $this;
        }
        
        protected function prepareNotification(Notification $notification, $params = array())
        {
            $notification->hydrate($params);
            
            return $notification;
        }
        
        /**
         * @return string
         */
        protected function getServerName()
        {
            $uname = posix_uname();
            
            return $uname['nodename'];
        }
    }
