<?php

require __DIR__ . '/../vendor/autoload.php';

use Fei\Service\Logger\Entity\Notification;
use Fei\Service\Logger\Client\Logger;

$start_time = microtime(true);

//$logger = new Logger([Logger::PARAMETER_BASEURL =>'http://logger.test.flash-global.net']);
//$logger = new Logger([Logger::PARAMETER_BASEURL =>'http://localhost:8080']);
$logger = new Logger([Logger::PARAMETER_BASEURL =>'http://192.168.0.198:8080']);
$logger->setTransport(new Fei\ApiClient\Transport\BasicTransport());

$notification = new Notification();
$notification->setMessage('Hello World!');
$notification->setLevel(Notification::LVL_ERROR);
$notification->setCategory(Notification::PERFORMANCE);
$notification->setContext(
   [
       "type" => 8,
       "message" => "Undefined variable: a",
       "file" => "C:\WWW\index.php",
       "line" => 2
   ]
);

/** @var \Fei\ApiClient\ResponseDescriptor $log */
$log = null;
$notify = function () use ($logger, $notification, &$log) {
    $log = $logger->notify($notification, ['context' => ['x' => 'y']]);
};

$notify();

$end_time = microtime(true);

if($log instanceof \Fei\ApiClient\ResponseDescriptor){
    print_r((string) $log->getBody());
    echo('Response '. $log->getCode(). PHP_EOL);
}else{
    echo "An error occurred.".PHP_EOL;
}

echo "time: ", bcsub($end_time, $start_time, 2), "\n";
