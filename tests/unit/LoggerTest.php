<?php

namespace Tests\Fei\Service\Logger\Client;

use Codeception\Test\Unit;
use Fei\ApiClient\Transport\SyncTransportInterface;
use Fei\Service\Logger\Entity\Notification;
use Fei\Service\Logger\Client\Logger;

class LoggerTest extends Unit
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

    public function testLoggerCanCommit()
    {
        $logger = new Logger();

        $transport = $this->createMock(SyncTransportInterface::class);
        $transport->expects($this->never())->method('sendMany');
        $logger->setTransport($transport);

        $logger->begin();
        $logger->notify('test');
        $logger->commit();
    }

    public function testLoggerCanDelay()
    {
        $logger = new Logger();

        $logger->begin();
        $this->assertAttributeEquals(true, 'isDelayed', $logger);
    }

    public function testLoggerCanNotify()
    {
        $logger = new Logger();
        $logger->setBaseUrl('http://azeaze.fr/');

        $notification = new Notification();
        $notification->setMessage($this->faker->sentence);
        $notification->setLevel(Notification::LVL_ERROR);

        $transport = $this->createMock(SyncTransportInterface::class);
        $transport->expects($this->once())->method('send');
        $logger->setTransport($transport);

        $logger->notify($notification);
    }

    public function testAutoCommit()
    {
        $logger = (new Logger())->enableAutoCommit();
        $logger->setBaseUrl('http://azeaze.fr/');

        $notification = new Notification();
        $notification->setMessage($this->faker->sentence);
        $notification->setLevel(Notification::LVL_ERROR);

        $transport = $this->createMock(SyncTransportInterface::class);
        $transport->expects($this->never())->method('send');
        $transport->expects($this->never())->method('sendMany');
        $logger->setTransport($transport);

        $logger->notify($notification);
    }

    public function testBacktrace()
    {
        $logger = new Logger();
        $logger->setBaseUrl('http://azeaze.fr/');

        $notification = new Notification();
        $notification->setMessage($this->faker->sentence);
        $notification->setLevel(Notification::LVL_ERROR);

        $logger->setTransport($this->createMock(SyncTransportInterface::class));

        $notify = function () use ($logger, $notification) {
            $logger->notify($notification);
        };

        $notify();

        $this->assertEquals(19, count($notification->getBackTrace()));
        $this->assertEquals(
            'Tests\Fei\Service\Logger\Client\LoggerTest->Tests\Fei\Service\Logger\Client\{closure}',
            $notification->getBackTrace()[0]['method']
        );
        $this->assertEquals(
            'Instance of Tests\Fei\Service\Logger\Client\LoggerTest',
            $notification->getBackTrace()[2]['args'][0]
        );
    }
}
