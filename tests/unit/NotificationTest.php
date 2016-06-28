<?php


use Pricer\Logger\Client\Notification;

class NotificationTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /** @var  Notification */
    protected $notification;

    /** @var  Faker\Generator */
    protected $faker;

    /** @var  \Pricer\Logger\Client\Logger */
    protected $logger;

    /** @var  \Pricer\WebClient\Transport\TransportInterface */
    protected $transport;

    public function testNotificationCanHold()
    {
        $this->assertFalse($this->logger->isOnHold());
        $this->notification->hold();
        $this->assertTrue($this->logger->isOnHold());
        $this->notification->flush();
        $this->assertFalse($this->logger->isOnHold());
    }

    public function testNotificationCanSend()
    {
        $this->notification->setCategory($this->tester->getRandomCategory());
        $this->notification->setContext(array($this->faker->words, $this->faker->words));
        $this->notification->hold();

        $this->notification->send();

        $this->assertNotEmpty($this->logger->getRequestStack());

    }

    public function testNotificationGettersSetters()
    {
        $category = $this->tester->getRandomCategory();
        $level = $this->tester->getRandomLevel();
        $context = array($this->faker->words, $this->faker->words);
        $location = $this->faker->url;
        $message = $this->faker->sentence;

        $this->notification->setCategory($category);
        $this->notification->setLevel($level);
        $this->notification->setContext($context);
        $this->notification->setLocation($location);
        $this->notification->setMessage($message);

        $this->assertEquals($category, $this->notification->getCategory());
        $this->assertEquals($level, $this->notification->getLevel());
        $this->assertEquals($context, $this->notification->getContext());
        $this->assertEquals($location, $this->notification->getLocation());
        $this->assertEquals($message, $this->notification->getMessage());
        $this->assertEquals($this->logger, $this->notification->getLogger());
    }
    

    protected function _before()
    {
        $this->faker = Faker\Factory::create();

        $this->notification = new Notification($this->faker->text(20), $this->tester->getRandomLevel());
        $this->logger = new \Pricer\Logger\Client\Logger();
        $this->logger->setLoggerUrl('http://localhost');
        // we need the AsyncTransport because the logger __destruct will send the message and we have to avoid him to do that
        $this->transport = new \Pricer\WebClient\Transport\AsyncTransport();
        $this->logger->setTransport($this->transport);
        $this->notification->setLogger($this->logger);
    }
}