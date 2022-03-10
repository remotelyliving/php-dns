<?php

namespace RemotelyLiving\PHPDNS\Tests\Unit\Observability\Events;

use RemotelyLiving\PHPDNS\Entities\Interfaces\Arrayable;
use RemotelyLiving\PHPDNS\Observability\Events\DNSQueryProfiled;
use RemotelyLiving\PHPDNS\Observability\Performance\Interfaces\ProfileInterface;
use RemotelyLiving\PHPDNS\Observability\Performance\Profile;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

class DNSQueryProfiledTest extends BaseTestAbstract
{
    private ProfileInterface $profile;

    private DNSQueryProfiled $DNSQueryProfiled;

    protected function setUp(): void
    {
        parent::setUp();

        $this->profile = $this->createMock(ProfileInterface::class);
        $this->DNSQueryProfiled = new DNSQueryProfiled($this->profile);
    }

    /**
     * @test
     */
    public function hasBasicGetters(): void
    {
        $this->assertSame($this->profile, $this->DNSQueryProfiled->getProfile());
        $this->assertSame(DNSQueryProfiled::NAME, $this->DNSQueryProfiled::getName());
    }

    /**
     * @test
     */
    public function isArrayable(): void
    {
        $this->profile->method('getElapsedSeconds')
            ->willReturn(100.1);

        $this->profile->method('getTransactionName')
            ->willReturn('transactionName');

        $this->profile->method('getPeakMemoryUsage')
            ->willReturn(123);

        $expected = [
            'elapsedSeconds' => 100.1,
            'transactionName' => 'transactionName',
            'peakMemoryUsage' => 123,
        ];

        $this->assertInstanceOf(Arrayable::class, $this->DNSQueryProfiled);
        $this->assertEquals($expected, $this->DNSQueryProfiled->toArray());
    }
}
