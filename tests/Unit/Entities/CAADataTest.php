<?php

namespace RemotelyLiving\PHPDNS\Tests\Unit\Entities;

use RemotelyLiving\PHPDNS\Entities\CAAData;
use RemotelyLiving\PHPDNS\Exceptions;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

use function serialize;
use function unserialize;

class CAADataTest extends BaseTestAbstract
{
    private int $flags = 0;

    private string $tag = 'issue';

    private string $value = '";"';

    private \RemotelyLiving\PHPDNS\Entities\CAAData $CAAData;

    protected function setUp(): void
    {
        parent::setUp();

        $this->CAAData = new CAAData($this->flags, $this->tag, $this->value);
    }

    /**
     * @test
     */
    public function knowsIfEquals(): void
    {
        $this->assertTrue($this->CAAData->equals($this->CAAData));
        $this->assertFalse($this->CAAData->equals(new CAAData(0, 'issue', 'boop.com')));
    }

    /**
     * @test
     */
    public function isArrayable(): void
    {
        $this->assertArrayableAndEquals(
            ['flags' => $this->flags, 'tag' => $this->tag, 'value' => ';'],
            $this->CAAData
        );
    }

    /**
     * @test
     */
    public function isJsonSerializable(): void
    {
        $this->assertJsonSerializeableAndEquals(
            ['flags' => $this->flags, 'tag' => $this->tag, 'value' => ';'],
            $this->CAAData
        );
    }

    /**
     * @test
     */
    public function isSerializable(): void
    {
        $this->assertSerializable($this->CAAData);
        $this->assertEquals($this->CAAData, unserialize(serialize($this->CAAData)));
    }

    /**
     * @test
     */
    public function isStringable(): void
    {
        $this->assertStringableAndEquals('0 issue ";"', $this->CAAData);
    }

    /**
     * @test
     */
    public function hasBasicGetters(): void
    {
        $this->assertSame($this->flags, $this->CAAData->getFlags());
        $this->assertSame($this->tag, $this->CAAData->getTag());
        $this->assertSame(';', $this->CAAData->getValue());

        $nullDefault = new CAAData(0, 'issue');
        $this->assertNull($nullDefault->getValue());
    }

    /**
     * @test
     */
    public function doesNotAllowSpaceCharactersAsValidValue(): void
    {
        $this->expectException(Exceptions\InvalidArgumentException::class);
        $badValue = '\'\\# 26 00 09 69 73 73 75 65 77 69 6c 64 6c 65 74 73 65 6e 63 72 79 70 74 2e 6f 72 67\'';
        new CAAData(0, 'issuewild', $badValue);
    }
}
