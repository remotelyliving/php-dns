<?php

namespace RemotelyLiving\PHPDNS\Tests\Unit\Entities;

use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Entities\PTRData;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

class PTRDataTest extends BaseTestAbstract
{
    private \RemotelyLiving\PHPDNS\Entities\Hostname $hostname;

    private \RemotelyLiving\PHPDNS\Entities\PTRData $PTRData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hostname = new Hostname('google.com');
        $this->PTRData = new PTRData($this->hostname);
    }

    /**
     * @test
     */
    public function knowsIfEquals(): void
    {
        $this->assertTrue($this->PTRData->equals($this->PTRData));
        $this->assertFalse($this->PTRData->equals(new PTRData(new Hostname('boop.com'))));
    }

    /**
     * @test
     */
    public function isArrayable(): void
    {
        $this->assertArrayableAndEquals(
            ['hostname' => (string)$this->hostname],
            $this->PTRData
        );
    }

    /**
     * @test
     */
    public function isJsonSerializable(): void
    {
        $this->assertJsonSerializeableAndEquals(
            ['hostname' => (string)$this->hostname],
            $this->PTRData
        );
    }

    /**
     * @test
     */
    public function isSerializable(): void
    {
        $this->assertSerializable($this->PTRData);
    }

    /**
     * @test
     */
    public function isStringable(): void
    {
        $this->assertStringableAndEquals('google.com.', $this->PTRData);
        $this->assertEquals($this->PTRData, unserialize(serialize($this->PTRData)));
    }

    /**
     * @test
     */
    public function hasBasicGetters(): void
    {
        $this->assertSame($this->hostname, $this->PTRData->getHostname());
    }
}
