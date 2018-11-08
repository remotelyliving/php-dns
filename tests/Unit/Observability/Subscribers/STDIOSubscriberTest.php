<?php
namespace RemotelyLiving\PHPDNS\Tests\Unit\Observability\Events;

use RemotelyLiving\PHPDNS\Observability\Events\DNSQueried;
use RemotelyLiving\PHPDNS\Observability\Events\DNSQueryFailed;
use RemotelyLiving\PHPDNS\Observability\Events\DNSQueryProfiled;
use RemotelyLiving\PHPDNS\Observability\Events\ObservableEventAbstract;
use RemotelyLiving\PHPDNS\Observability\Subscribers\STDIOSubscriber;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

class STDIOSubscriberTest extends BaseTestAbstract
{
    /**
     * @var \SplFileObject
     */
    private $STDOut;

    /**
     * @var \SplFileObject
     */
    private $STDErr;

    /**
     * @var \RemotelyLiving\PHPDNS\Observability\Events\ObservableEventAbstract
     */
    private $event;

    /**
     * @var \RemotelyLiving\PHPDNS\Observability\Subscribers\STDIOSubscriber
     */
    private $subscriber;

    /**
     * @var string
     */
    private $expectedOut;

    protected function setUp()
    {
        parent::setUp();

        $this->STDOut = $this->getMockBuilder(\SplFileObject::class)
            ->setConstructorArgs(['php://memory'])
            ->setMethods(['fwrite'])
            ->getMock();

        $this->STDErr = $this->getMockBuilder(\SplFileObject::class)
            ->setConstructorArgs(['php://memory'])
            ->setMethods(['fwrite'])
            ->getMock();

        $this->event = new class extends ObservableEventAbstract
        {
            public static function getName(): string
            {
                return 'the name';
            }

            public function toArray(): array
            {
                return ['beep' => 'boop'];
            }
        };

        $this->expectedOut = <<<EOF
{
    "the name": {
        "beep": "boop"
    }
}

EOF;
        $this->subscriber = new STDIOSubscriber($this->STDOut, $this->STDErr);
    }

    /**
     * @test
     */
    public function getsSubscribedEvents()
    {
        $this->assertEquals([
            DNSQueryFailed::getName() => 'onDNSQueryFailed',
            DNSQueried::getName() => 'onDNSQueried',
            DNSQueryProfiled::getName() => 'onDNSQueryProfiled',
        ], $this->subscriber::getSubscribedEvents());
    }

    /**
     * @test
     */
    public function writesToSTDErrOnQueryFailure()
    {


        $this->STDErr->expects($this->once())
            ->method('fwrite')
            ->with($this->expectedOut);

        $this->subscriber->onDNSQueryFailed($this->event);
    }

    /**
     * @test
     */
    public function writesToSTDOutOnQuery()
    {
        $this->STDOut->expects($this->once())
            ->method('fwrite')
            ->with($this->expectedOut);

        $this->subscriber->onDNSQueried($this->event);
    }

    /**
     * @test
     */
    public function writesToSTDOutOnQueryProfiled()
    {
        $this->STDOut->expects($this->once())
            ->method('fwrite')
            ->with($this->expectedOut);

        $this->subscriber->onDNSQueryProfiled($this->event);
    }
}
