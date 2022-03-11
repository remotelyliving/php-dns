<?php

namespace RemotelyLiving\PHPDNS\Tests\Unit\Entities;

use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Entities\MXData;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

use function serialize;
use function unserialize;

class MXDataTest extends BaseTestAbstract
{
    private \RemotelyLiving\PHPDNS\Entities\Hostname $target;

    private int $priority = 60;

    private \RemotelyLiving\PHPDNS\Entities\MXData $MXData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->target = new Hostname('google.com');
        $this->MXData = new MXData($this->target, $this->priority);
    }

    /**
     * @test
     */
    public function knowsIfEquals(): void
    {
        $this->assertTrue($this->MXData->equals($this->MXData));
        $this->assertFalse($this->MXData->equals(new MXData(new Hostname('boop.com'), 60)));
    }

    /**
     * @test
     */
    public function isArrayable(): void
    {
        $this->assertArrayableAndEquals(
            ['target' => (string)$this->target, 'priority' => $this->priority],
            $this->MXData
        );
    }

    /**
     * @test
     */
    public function isJsonSerializeable(): void
    {
        $this->assertJsonSerializeableAndEquals(
            ['target' => (string)$this->target, 'priority' => $this->priority],
            $this->MXData
        );
    }

    /**
     * @test
     */
    public function isSerializable(): void
    {
        $this->assertSerializable($this->MXData);
        $this->assertEquals($this->MXData, unserialize(serialize($this->MXData)));
    }

    /**
     * @test
     */
    public function isStringable(): void
    {
        $this->assertStringableAndEquals('60 google.com.', $this->MXData);
    }

    /**
     * @test
     */
    public function hasBasicGetters(): void
    {
        $this->assertSame($this->target, $this->MXData->getTarget());
        $this->assertSame($this->priority, $this->MXData->getPriority());
    }
}
