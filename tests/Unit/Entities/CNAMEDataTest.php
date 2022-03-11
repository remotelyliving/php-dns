<?php

namespace RemotelyLiving\PHPDNS\Tests\Unit\Entities;

use RemotelyLiving\PHPDNS\Entities\CNAMEData;
use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

use function serialize;
use function unserialize;

class CNAMEDataTest extends BaseTestAbstract
{
    private \RemotelyLiving\PHPDNS\Entities\Hostname $hostname;

    private \RemotelyLiving\PHPDNS\Entities\CNAMEData $CNAMEData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hostname = new Hostname('google.com');
        $this->CNAMEData = new CNAMEData($this->hostname);
    }

    /**
     * @test
     */
    public function knowsIfEquals(): void
    {
        $this->assertTrue($this->CNAMEData->equals($this->CNAMEData));
        $this->assertFalse($this->CNAMEData->equals(new CNAMEData(new Hostname('boop.com'))));
    }

    /**
     * @test
     */
    public function isArrayable(): void
    {
        $this->assertArrayableAndEquals(
            ['hostname' => (string)$this->hostname],
            $this->CNAMEData
        );
    }

    /**
     * @test
     */
    public function isJsonSerializable(): void
    {
        $this->assertJsonSerializeableAndEquals(
            ['hostname' => (string)$this->hostname],
            $this->CNAMEData
        );
    }

    /**
     * @test
     */
    public function isSerializable(): void
    {
        $this->assertSerializable($this->CNAMEData);
    }

    /**
     * @test
     */
    public function isStringable(): void
    {
        $this->assertStringableAndEquals('google.com.', $this->CNAMEData);
        $this->assertEquals($this->CNAMEData, unserialize(serialize($this->CNAMEData)));
    }

    /**
     * @test
     */
    public function hasBasicGetters(): void
    {
        $this->assertSame($this->hostname, $this->CNAMEData->getHostname());
    }
}
