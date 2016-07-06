<?php

use Fei\ApiClient\RequestDescriptor;
use Fei\ApiClient\Transport\TransportInterface;
use Fei\Service\Logger\Entity\Notification;
use Pricer\Logger\Client\Logger;

class LoggerTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /** @var  Logger */
    protected $logger;

    /** @var  Faker\Generator */
    protected $faker;

    protected function _before()
    {
        $this->faker = \Faker\Factory::create('fr_FR');
    }

    public function testLoggerCanFlush()
    {
        $logger = new Logger();

        $request = $this->createMock(RequestDescriptor::class);
        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->once())->method('send')->with($request);
        $logger->setTransport($transport);

        $logger->hold();
        $logger->send($request);
        $logger->flush();
    }

    public function testLoggerCanHold()
    {
        $logger = new Logger();

        $logger->hold();
        $this->assertAttributeEquals(true, 'isDelayed', $logger);
    }

    public function testLoggerCanNotify()
    {
        $logger = new Logger();
        $logger->setBaseUrl('http://azeaze.fr/');

        $notification = new Notification();
        $notification->setMessage($this->faker->sentence);
        $notification->setLevel(Notification::INFO);

        $transport = $this->createMock(TransportInterface::class);
        $transport->expects($this->once())->method('send');
        $logger->setTransport($transport);

        $logger->notify($notification);
    }

    public function testLoggerServerUrl()
    {
        $logger = new Logger();

        putenv('APP_ENV=prod');
        $this->assertEquals('http://logger.test.flash-global.net', $logger->getServerUrl());

        putenv('APP_ENV=test');
        $this->assertEquals('http://192.168.5.110:8080', $logger->getServerUrl());

        putenv('APP_ENV=dev');
        $this->assertEquals('http://logger.test.flash-global.net', $logger->getServerUrl());

        putenv('APP_ENV=other');
        $this->assertEquals('http://logger.test.flash-global.net', $logger->getServerUrl());

    }

}