<?php

namespace RemotelyLiving\PHPDNS\Tests\Unit\Observability\Events;

use RemotelyLiving\PHPDNS\Observability\Events\DNSQueried;
use RemotelyLiving\PHPDNS\Observability\Events\DNSQueryFailed;
use RemotelyLiving\PHPDNS\Observability\Events\DNSQueryProfiled;
use RemotelyLiving\PHPDNS\Observability\Events\ObservableEventAbstract;
use RemotelyLiving\PHPDNS\Observability\Subscribers\STDIOSubscriber;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;
use SplFileObject;

class STDIOSubscriberTest extends BaseTestAbstract
{
    private \SplFileObject $STDOut;

    private \SplFileObject $STDErr;

    private \RemotelyLiving\PHPDNS\Observability\Events\ObservableEventAbstract $event;

    private \RemotelyLiving\PHPDNS\Observability\Subscribers\STDIOSubscriber $subscriber;

    private string $expectedOut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->STDOut = $this->getMockBuilder(SplFileObject::class)
            ->setConstructorArgs(['php://memory'])
            ->setMethods(['fwrite'])
            ->getMock();

        $this->STDErr = $this->getMockBuilder(SplFileObject::class)
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
    public function getsSubscribedEvents(): void
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
    public function writesToSTDErrOnQueryFailure(): void
    {


        $this->STDErr->expects($this->once())
            ->method('fwrite')
            ->with($this->expectedOut);

        $this->subscriber->onDNSQueryFailed($this->event);
    }

    /**
     * @test
     */
    public function writesToSTDOutOnQuery(): void
    {
        $this->STDOut->expects($this->once())
            ->method('fwrite')
            ->with($this->expectedOut);

        $this->subscriber->onDNSQueried($this->event);
    }

    /**
     * @test
     */
    public function writesToSTDOutOnQueryProfiled(): void
    {
        $this->STDOut->expects($this->once())
            ->method('fwrite')
            ->with($this->expectedOut);

        $this->subscriber->onDNSQueryProfiled($this->event);
    }
}
