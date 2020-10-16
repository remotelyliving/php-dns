<?php

namespace RemotelyLiving\PHPDNS\Tests\Unit;

use JsonSerializable;
use PHPUnit\Framework\TestCase;
use RemotelyLiving\PHPDNS\Entities\Interfaces\Arrayable;
use RemotelyLiving\PHPDNS\Entities\Interfaces\Serializable;

use function serialize;
use function unserialize;

abstract class BaseTestAbstract extends TestCase
{
    protected function assertSerializable(Serializable $serializable)
    {
        $this->assertEquals($serializable, unserialize(serialize($serializable)));
    }

    protected function assertArrayableAndEquals(array $expected, Arrayable $arrayable)
    {
        $this->assertEquals($expected, $arrayable->toArray());
    }

    protected function assertJsonSerializeableAndEquals(array $expected, JsonSerializable $jsonSerializeable)
    {
        $this->assertEquals($expected, $jsonSerializeable->jsonSerialize());
    }

    protected function assertStringableAndEquals(string $expected, string $stringable)
    {
        $this->assertSame($expected, $stringable);
    }
}
