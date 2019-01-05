<?php
namespace RemotelyLiving\PHPDNS\Tests\Unit\Entities;

use RemotelyLiving\PHPDNS\Entities\TXTData;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

class TXTDataTest extends BaseTestAbstract
{
    /**
     * @var string
     */
    private $value = 'boop';

    /**
     * @var \RemotelyLiving\PHPDNS\Entities\TXTData
     */
    private $TXTData;

    protected function setUp()
    {
        parent::setUp();

        $this->TXTData = new TXTData($this->value);
    }

    /**
     * @test
     */
    public function knowsIfEquals()
    {
        $this->assertTrue($this->TXTData->equals($this->TXTData));
        $this->assertFalse($this->TXTData->equals(new TXTData('beep')));
    }

    /**
     * @test
     */
    public function isArrayable()
    {
        $this->assertArrayableAndEquals(['value' => $this->value], $this->TXTData);
    }

    /**
     * @test
     */
    public function isSerializable()
    {
        $this->assertSerializable($this->TXTData);
        $this->assertEquals($this->TXTData, \unserialize(\serialize($this->TXTData)));
    }

    /**
     * @test
     */
    public function isStringable()
    {
        $this->assertStringableAndEquals('boop', $this->TXTData);
    }

    /**
     * @test
     */
    public function hasBasicGetters()
    {
        $this->assertSame($this->value, $this->TXTData->getValue());
    }
}
