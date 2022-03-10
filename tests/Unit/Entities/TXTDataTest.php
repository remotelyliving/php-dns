<?php

namespace RemotelyLiving\PHPDNS\Tests\Unit\Entities;

use RemotelyLiving\PHPDNS\Entities\TXTData;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

use function serialize;
use function unserialize;

class TXTDataTest extends BaseTestAbstract
{
    private string $value = 'boop';

    private \RemotelyLiving\PHPDNS\Entities\TXTData $TXTData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->TXTData = new TXTData($this->value);
    }

    /**
     * @test
     */
    public function knowsIfEquals(): void
    {
        $this->assertTrue($this->TXTData->equals($this->TXTData));
        $this->assertFalse($this->TXTData->equals(new TXTData('beep')));
    }

    /**
     * @test
     */
    public function isArrayable(): void
    {
        $this->assertArrayableAndEquals(['value' => $this->value], $this->TXTData);
    }

    /**
     * @test
     */
    public function isJsonSerializeable(): void
    {
        $this->assertJsonSerializeableAndEquals(['value' => $this->value], $this->TXTData);
    }

    /**
     * @test
     */
    public function isSerializable(): void
    {
        $this->assertSerializable($this->TXTData);
        $this->assertEquals($this->TXTData, unserialize(serialize($this->TXTData)));
    }

    /**
     * @test
     */
    public function isStringable(): void
    {
        $this->assertStringableAndEquals('boop', $this->TXTData);
    }

    /**
     * @test
     */
    public function hasBasicGetters(): void
    {
        $this->assertSame($this->value, $this->TXTData->getValue());
    }
}
