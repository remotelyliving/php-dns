<?php

namespace RemotelyLiving\PHPDNS\Tests\Unit\Entities;

use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Entities\NSData;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

use function serialize;
use function unserialize;

class NSDataTest extends BaseTestAbstract
{
    private \RemotelyLiving\PHPDNS\Entities\Hostname $target;

    private \RemotelyLiving\PHPDNS\Entities\NSData $NSData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->target = new Hostname('google.com');
        $this->NSData = new NSData($this->target);
    }

    /**
     * @test
     */
    public function knowsIfEquals(): void
    {
        $this->assertTrue($this->NSData->equals($this->NSData));
        $this->assertFalse($this->NSData->equals(new NSData(new Hostname('boop.com'))));
    }

    /**
     * @test
     */
    public function isArrayable(): void
    {
        $this->assertArrayableAndEquals(['target' => (string)$this->target], $this->NSData);
    }

    /**
     * @test
     */
    public function isJsonSerializeable(): void
    {
        $this->assertJsonSerializeableAndEquals(['target' => (string)$this->target], $this->NSData);
    }

    /**
     * @test
     */
    public function isSerializable(): void
    {
        $this->assertSerializable($this->NSData);
        $this->assertEquals($this->NSData, unserialize(serialize($this->NSData)));
    }

    /**
     * @test
     */
    public function isStringable(): void
    {
        $this->assertStringableAndEquals('google.com.', $this->NSData);
    }

    /**
     * @test
     */
    public function hasBasicGetters(): void
    {
        $this->assertSame($this->target, $this->NSData->getTarget());
    }
}
