<?php
namespace RemotelyLiving\PHPDNS\Tests\Unit\Entities;

use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Entities\NSData;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

class NSDataTest extends BaseTestAbstract
{
    /**
     * @var \RemotelyLiving\PHPDNS\Entities\Hostname
     */
    private $target;

    /**
     * @var \RemotelyLiving\PHPDNS\Entities\NSData
     */
    private $NSData;

    protected function setUp()
    {
        parent::setUp();

        $this->target = new Hostname('google.com');
        $this->NSData = new NSData($this->target);
    }

    /**
     * @test
     */
    public function knowsIfEquals()
    {
        $this->assertTrue($this->NSData->equals($this->NSData));
        $this->assertFalse($this->NSData->equals(new NSData(new Hostname('boop.com'))));
    }

    /**
     * @test
     */
    public function isArrayable()
    {
        $this->assertArrayableAndEquals(['target' => (string)$this->target], $this->NSData);
    }

    /**
     * @test
     */
    public function isSerializable()
    {
        $this->assertSerializable($this->NSData);
        $this->assertEquals($this->NSData, \unserialize(\serialize($this->NSData)));
    }

    /**
     * @test
     */
    public function isStringable()
    {
        $this->assertStringableAndEquals('google.com.', $this->NSData);
    }

    /**
     * @test
     */
    public function hasBasicGetters()
    {
        $this->assertSame($this->target, $this->NSData->getTarget());
    }
}
