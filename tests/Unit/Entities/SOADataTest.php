<?php

namespace RemotelyLiving\PHPDNS\Tests\Unit\Entities;

use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Entities\SOAData;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

use function serialize;
use function unserialize;

class SOADataTest extends BaseTestAbstract
{
    private \RemotelyLiving\PHPDNS\Entities\Hostname $mname;

    private \RemotelyLiving\PHPDNS\Entities\Hostname $rname;

    private int $serial = 2342;

    private int $refresh = 123;

    private int $retry = 321;

    private int $expire = 3434;

    private int $minTTL = 60;

    private \RemotelyLiving\PHPDNS\Entities\SOAData $SOAData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mname = new Hostname('google.com');
        $this->rname = new Hostname('facebook.com');
        $this->SOAData = new SOAData(
            $this->mname,
            $this->rname,
            $this->serial,
            $this->refresh,
            $this->retry,
            $this->expire,
            $this->minTTL
        );
    }

    /**
     * @test
     */
    public function knowsIfEquals(): void
    {
        $anotherSOA = new SOAData($this->rname, $this->mname, 1, 1, 1, 1, 1);
        $this->assertTrue($this->SOAData->equals($this->SOAData));
        $this->assertFalse($this->SOAData->equals($anotherSOA));
    }

    /**
     * @test
     */
    public function isArrayable(): void
    {
        $this->assertArrayableAndEquals(
            [
                'rname' => (string)$this->rname,
                'mname' => (string)$this->mname,
                'serial' => $this->serial,
                'refresh' => $this->refresh,
                'retry' => $this->retry,
                'expire' => $this->expire,
                'minimumTTL' => $this->minTTL,
            ],
            $this->SOAData
        );
    }

    /**
     * @test
     */
    public function isJsonSerializeable(): void
    {
        $this->assertJsonSerializeableAndEquals(
            [
                'rname' => (string)$this->rname,
                'mname' => (string)$this->mname,
                'serial' => $this->serial,
                'refresh' => $this->refresh,
                'retry' => $this->retry,
                'expire' => $this->expire,
                'minimumTTL' => $this->minTTL,
            ],
            $this->SOAData
        );
    }

    /**
     * @test
     */
    public function isSerializable(): void
    {
        $this->assertSerializable($this->SOAData);
        $this->assertEquals($this->SOAData, unserialize(serialize($this->SOAData)));
    }

    /**
     * @test
     */
    public function isStringable(): void
    {
        $this->assertStringableAndEquals('google.com. facebook.com. 2342 123 321 3434 60', $this->SOAData);
    }

    /**
     * @test
     */
    public function hasBasicGetters(): void
    {
        $this->assertSame($this->mname, $this->SOAData->getMname());
        $this->assertSame($this->rname, $this->SOAData->getRname());
        $this->assertSame($this->serial, $this->SOAData->getSerial());
        $this->assertSame($this->refresh, $this->SOAData->getRefresh());
        $this->assertSame($this->retry, $this->SOAData->getRetry());
        $this->assertSame($this->expire, $this->SOAData->getExpire());
        $this->assertSame($this->minTTL, $this->SOAData->getMinTTL());
    }
}
