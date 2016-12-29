<?php

require __DIR__ . '/../vendor/autoload.php';

use Fei\Service\Logger\Entity\Notification;
use Fei\Service\Logger\Client\Logger;

$start_time = microtime(true);

//$logger = new Logger([Logger::OPTION_BASEURL =>'http://logger.test.flash-global.net']);
$logger = new Logger([Logger::OPTION_BASEURL =>'http://192.168.1.111:8020']);
$logger->setTransport(new Fei\ApiClient\Transport\BasicTransport());

$criteria = array('toto' => 'tata');

/** @var \Fei\ApiClient\ResponseDescriptor $log */
$log = null;
$retrieve = function () use ($logger, $criteria, &$log) {
    $log = $logger->retrieve(array (
        'notification_command' => '%opc%',
        'notification_command_operator' => 'like'
    ));
};

$retrieve();
$end_time = microtime(true);

if ($log instanceof \Fei\ApiClient\ResponseDescriptor) {
    print_r(json_decode((string) $log->getBody(), true));
    echo('Response '. $log->getCode(). PHP_EOL);
} else {
    echo "An error occurred.".PHP_EOL;
}

echo "time: ", bcsub($end_time, $start_time, 2), "\n";
