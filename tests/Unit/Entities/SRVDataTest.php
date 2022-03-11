<?php

namespace RemotelyLiving\PHPDNS\Tests\Unit\Entities;

use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Entities\SRVData;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

use function serialize;
use function unserialize;

class SRVDataTest extends BaseTestAbstract
{
    private int $priority = 100;

    private int $weight = 200;

    private int $port = 9090;

    private string $target = 'target.co.';

    private \RemotelyLiving\PHPDNS\Entities\SRVData $SRVData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->SRVData = new SRVData($this->priority, $this->weight, $this->port, new Hostname($this->target));
    }

    /**
     * @test
     */
    public function knowsIfEquals(): void
    {
        $this->assertTrue($this->SRVData->equals($this->SRVData));
        $this->assertFalse($this->SRVData->equals(new SRVData(1, 2, 3, new Hostname('thing.co.'))));
    }

    /**
     * @test
     */
    public function isArrayable(): void
    {
        $this->assertArrayableAndEquals(
            [
                'priority' => $this->priority,
                'weight' => $this->weight,
                'port' => $this->port,
                'target' => $this->target,
            ],
            $this->SRVData
        );
    }

    /**
     * @test
     */
    public function isJsonSerializeable(): void
    {
        $this->assertJsonSerializeableAndEquals(
            [
            'priority' => $this->priority,
            'weight' => $this->weight,
            'port' => $this->port,
            'target' => $this->target,
                        ],
            $this->SRVData
        );
    }

    /**
     * @test
     */
    public function isSerializable(): void
    {
        $this->assertSerializable($this->SRVData);
        $this->assertEquals($this->SRVData, unserialize(serialize($this->SRVData)));
    }

    /**
     * @test
     */
    public function isStringable(): void
    {
        $this->assertStringableAndEquals('100 200 9090 target.co.', $this->SRVData);
    }

    /**
     * @test
     */
    public function hasBasicGetters(): void
    {
        $this->assertSame($this->priority, $this->SRVData->getPriority());
        $this->assertSame($this->weight, $this->SRVData->getWeight());
        $this->assertSame($this->port, $this->SRVData->getPort());
        $this->assertSame($this->target, $this->SRVData->getTarget()->__toString());
    }
}
