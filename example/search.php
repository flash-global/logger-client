<?php

require __DIR__ . '/../vendor/autoload.php';

use Fei\Service\Logger\Client\Builder\SearchBuilder;
use Fei\Service\Logger\Client\Logger;

$start_time = microtime(true);

$logger = new Logger([Logger::OPTION_BASEURL =>'http://127.0.0.1:80']);
$logger->setTransport(new Fei\ApiClient\Transport\BasicTransport());
$logger->setAuthorization('key');

$builder = new SearchBuilder();
$builder->message()->beginsWith('Call');
$builder->context()->key('reference')->equal('error-590b898c6ae764.44021859');
$builder->reportedAt()->equal('2017-05-04');

/** @var \Fei\ApiClient\ResponseDescriptor $log */
$log = null;
$retrieve = function () use ($logger, $builder, &$log) {
    $log = $logger->retrieve($builder);
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
