<?php
/**
 * Created by PhpStorm.
 * User: Neofox
 * Date: 21/06/2016
 * Time: 14:13
 */

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/webclients/vendor/autoload.php';


use Pricer\Logger\Client\Logger;
use Pricer\WebClient\Transport\AsyncTransport;

$start_time = microtime(true);

$baseurl = "http://httpbin.org";


$logger = new Logger();

$transporter = new AsyncTransport();
$logger->setTransport($transporter);

$request = $transporter->post(json_encode(array('test' => 123)), $baseurl . '/post');

/** @var \Amp\Artax\Response $response */
$response = \Amp\wait($transporter->send($request));
echo($response->getBody());


$end_time = microtime(true);

echo "time: ", bcsub($end_time, $start_time, 2), "\n";