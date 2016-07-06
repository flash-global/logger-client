<?php
/**
 * Created by PhpStorm.
 * User: Neofox
 * Date: 21/06/2016
 * Time: 14:13
 */

require __DIR__ . '/../vendor/autoload.php';

use Fei\Service\Logger\Entity\Notification;
use Pricer\Logger\Client\Logger;

$start_time = microtime(true);

$logger = new Logger([Logger::PARAMETER_BASEURL =>'http://localhost:8080/']);
$logger->setTransport(new Fei\ApiClient\Transport\BasicTransport());

$notification = new Notification();
$notification->setMessage('Hello World!');
$notification->setLevel(Notification::DEBUG);
$notification->setCategory(Notification::PERFORMANCE);

/** @var \Fei\ApiClient\ResponseDescriptor $log */
$log = $logger->notify($notification);

$end_time = microtime(true);

if($log instanceof \Fei\ApiClient\ResponseDescriptor){
    echo('Response '. $log->getCode(). PHP_EOL);
}else{
    echo "An error occured.".PHP_EOL;
}

echo "time: ", bcsub($end_time, $start_time, 2), "\n";
