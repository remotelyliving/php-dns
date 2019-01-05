<?php
namespace RemotelyLiving\PHPDNS\Tests\Unit\Entities;

use RemotelyLiving\PHPDNS\Entities\CNAMEData;
use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

class CNAMEDataTest extends BaseTestAbstract
{
    /**
     * @var \RemotelyLiving\PHPDNS\Entities\Hostname
     */
    private $hostname;

    /**
     * @var \RemotelyLiving\PHPDNS\Entities\CNAMEData
     */
    private $CNAMEData;

    protected function setUp()
    {
        parent::setUp();

        $this->hostname = new Hostname('google.com');
        $this->CNAMEData = new CNAMEData($this->hostname);
    }

    /**
     * @test
     */
    public function knowsIfEquals()
    {
        $this->assertTrue($this->CNAMEData->equals($this->CNAMEData));
        $this->assertFalse($this->CNAMEData->equals(new CNAMEData(new Hostname('boop.com'))));
    }

    /**
     * @test
     */
    public function isArrayable()
    {
        $this->assertArrayableAndEquals(
            ['hostname' => (string)$this->hostname],
            $this->CNAMEData
        );
    }

    /**
     * @test
     */
    public function isSerializable()
    {
        $this->assertSerializable($this->CNAMEData);
    }

    /**
     * @test
     */
    public function isStringable()
    {
        $this->assertStringableAndEquals('google.com.', $this->CNAMEData);
        $this->assertEquals($this->CNAMEData, \unserialize(\serialize($this->CNAMEData)));
    }

    /**
     * @test
     */
    public function hasBasicGetters()
    {
        $this->assertSame($this->hostname, $this->CNAMEData->getHostname());
    }
}
