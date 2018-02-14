<?php

namespace Tests\Fei\Service\Logger\Client\Psr;

use Fei\Service\Logger\Client\Logger;
use Fei\Service\Logger\Client\Psr\ContextExtractor;
use Fei\Service\Logger\Client\Psr\PsrLoggerAdapter;
use Fei\Service\Logger\Entity\Notification;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

/**
 * Class PsrLoggerAdapter
 *
 * @package Tests\Fei\Service\Logger\Client\Psr
 */
class PsrLoggerAdapterTest extends TestCase
{
    public function testClientAccessors()
    {
        $client = new Logger();
        $adapter = new PsrLoggerAdapter($client);

        $this->assertEquals($client, $adapter->getClient());
        $this->assertAttributeEquals($adapter->getClient(), 'client', $adapter);
    }

    public function testContextExtractor()
    {
        $client = new Logger();
        $adapter = new PsrLoggerAdapter($client);

        $this->assertInstanceOf(ContextExtractor::class, $adapter->getExtractor());

        $adapter->setExtractor(new ContextExtractor());

        $this->assertEquals(new ContextExtractor(), $adapter->getExtractor());
        $this->assertAttributeEquals($adapter->getExtractor(), 'extractor', $adapter);
    }

    public function testLog()
    {
        $message = 'this is a test';

        $client = $this->createMock(Logger::class);
        $client->expects($this->once())->method('notify')->with($message, ['level' => Notification::LVL_WARNING]);

        $adapter = new PsrLoggerAdapter($client);

        $adapter->log(LogLevel::WARNING, $message);
    }

    /**
     * @dataProvider dataForTestLevel
     *
     * @param string $level
     * @param int $loggerLevel
     */
    public function testLevel($level, $loggerLevel)
    {
        $message = 'this is a test';

        $client = $this->createMock(Logger::class);
        $client->expects($this->once())->method('notify')->with($message, ['level' => $loggerLevel]);

        $adapter = new PsrLoggerAdapter($client);

        $adapter->$level($message);
    }

    public function dataForTestLevel()
    {
        return [
            0 => [LogLevel::EMERGENCY, Notification::LVL_PANIC],
            1 => [LogLevel::ALERT, Notification::LVL_PANIC],
            2 => [LogLevel::CRITICAL, Notification::LVL_PANIC],
            3 => [LogLevel::ERROR, Notification::LVL_ERROR],
            4 => [LogLevel::WARNING, Notification::LVL_WARNING],
            5 => [LogLevel::NOTICE, Notification::LVL_INFO],
            6 => [LogLevel::INFO, Notification::LVL_INFO],
            7 => [LogLevel::DEBUG, Notification::LVL_DEBUG]
        ];
    }
}

