<?php
/**
 * Created by PhpStorm.
 * User: Neofox
 * Date: 21/06/2016
 * Time: 13:42
 */

namespace Pricer\Logger\Client;


class Logger extends AbstractLogger
{
    // category
    const CATEGORY_SECURITY    = 1;
    const CATEGORY_PERFORMANCE = 2;
    const CATEGORY_BUSINESS    = 4;

    // level
    const LEVEL_DEBUG   = 1;
    const LEVEL_INFO    = 2;
    const LEVEL_WARNING = 4;
    const LEVEL_ERROR   = 8;
    const LEVEL_PANIC   = 16;

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
        $this->loggerUrl = $this->getServerUrl();
        $this->exceptionLogFile = '/tmp/logger.log';
        $this->filterLevel = !empty($options['filter']) ? $options['filter'] : self::LEVEL_DEBUG;

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

    /**
     * @param $message
     * @param $level
     *
     * @return Notification
     */
    public function notify($message, $level)
    {
        $notification = new Notification($message, $level);
        $notification->setLogger($this);

        return $notification;
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
        $response = false;
        $this->setHold(false);
        $requestStack = $this->getRequestStack();
        if (!empty($requestStack)) {
            try {
                $response = $this->getTransport()->sendMany($this->getRequestStack());

            } catch (\Exception $e) {
                throw $e;
            }
        }
        $this->setRequestStack(array());

        return $response;
    }

    /**
     * @param            $message
     * @param            $level
     * @param array|null $params
     *????
     * @return $this|\Amp\Promise|\Guzzle\Http\Message\Response
     * @throws \Exception
     */
    public function log($message, $level, array $params = null)
    {
        $category = !empty($params['category']) ? $params['category'] : self::CATEGORY_BUSINESS;
        $context = !empty($params['context']) ? $params['context'] : array();
        $location = !empty($params['location']) ? $params['location'] : $this->guessPath();
        $env = getenv('APP_ENV') ?: 'prod';
        $login = isset($_SESSION['login']) ? $_SESSION['login'] : '';
        $command = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : realpath($_SERVER['_']) . ' ' . implode(' ',
                $_SERVER['argv']);

        $this->enhanceContext($context);

        $transporter = $this->getTransport();
        if(empty($transporter)) throw new \Exception("a Transporter as to be set.");

        $request = $transporter->post(array(
            'message'   => $message,
            'context'   => $context,
            'origin'    => 'http',
            'level'     => (int)$level,
            'location'  => $location,
            'server'    => $this->getServerName(),
            'user'      => $this->espaceString($login),
            'command'   => $this->espaceString($command),
            'env'       => $env,
            'category'  => $category,
            'backtrace' => json_encode(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3)),
        ), $this->loggerUrl . '/api/notification');

        if ($level >= $this->filterLevel) {
            if ($this->isOnHold()) {
                $this->requestStack[] = $request;
            } else {
                try {
                    return $transporter->send($request);
                } catch (\Exception $e) {
                    file_put_contents($this->exceptionLogFile, $e, FILE_APPEND);
                }
            }
        }

        return $this;
    }

    /**
     * Try to guess from where the log come if it was no explicity declared
     *
     * @return string
     */
    protected function guessPath()
    {
        $location = '';
        $stack = debug_backtrace();
        $firstFrame = $stack[count($stack) - 1];
        $initialFile = $firstFrame['file'];

        // check if pricer
        if (stripos($initialFile, 'intranet') !== false) {
            $location = "pricer";
        }
        // check if pricer
        if (stripos($initialFile, 'www/FLASH/extrafourn') !== false) {
            $location = "supplier-portal";
        }
        // check if pricer
        if (stripos($initialFile, 'www/FLASH/customerportal') !== false) {
            $location = "customer-portal";
        }

        return $location;
    }

    public function enhanceContext(array $context)
    {
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        $mergeContext = $context + array(
                'referer_url' => $this->espaceString($referer),
            );

        $this->context = json_encode($mergeContext);

        return $this;
    }

    protected function espaceString($string)
    {
        $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');

        return htmlentities($string, ENT_QUOTES, 'UTF-8');
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