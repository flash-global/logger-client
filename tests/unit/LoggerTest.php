<?php


use Pricer\WebClient\Transport\AsyncTransport;

class LoggerTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /** @var  \Pricer\Logger\Client\Logger */
    protected $logger;

    /** @var  Faker\Generator */
    protected $faker;

    protected function _before()
    {
        $this->faker = \Faker\Factory::create('fr_FR');

        $this->logger = new Pricer\Logger\Client\Logger();
        $this->logger->setBaseUrl('http:/localhost');
        $this->logger->setTransport(new AsyncTransport());
    }

    // tests
    public function testLoggerIsLogging()
    {
        $this->assertFalse($this->logger->isOnHold());
        $this->logger->log($this->faker->sentence, $this->tester->getRandomLevel());
    }

    public function testLoggerGettersSetters()
    {
        $this->logger->setBaseUrl('http://httpbin.org/');

        $this->assertEquals('http://httpbin.org/', $this->logger->getBaseUrl());
        $this->assertEquals('http://httpbin.org/', $this->logger->getBaseUrl());

    }

    public function testLoggerCanFlush()
    {
        $this->logger->hold();
        $this->logger->log($this->faker->sentence, $this->tester->getRandomLevel());

        $this->assertArrayHasKey(0, $this->logger->getRequestStack());
        $this->assertTrue($this->logger->isOnHold());

        $this->logger->flush();

        $this->assertFalse($this->logger->isOnHold());
    }

    public function testLoggerCanNotify()
    {
        $this->logger->notify($this->faker->sentence, $this->tester->getRandomLevel());
    }

    public function testLoggerServerUrl()
    {
        putenv('APP_ENV=prod');
        $this->assertEquals('http://logger.test.flash-global.net', $this->logger->getServerUrl());

        putenv('APP_ENV=test');
        $this->assertEquals('http://192.168.5.110:8080', $this->logger->getServerUrl());

        putenv('APP_ENV=dev');
        $this->assertEquals('http://logger.test.flash-global.net', $this->logger->getServerUrl());

        putenv('APP_ENV=other');
        $this->assertEquals('http://logger.test.flash-global.net', $this->logger->getServerUrl());

    }

    public function _after()
    {
        $this->logger->__destruct();
    }
    
}