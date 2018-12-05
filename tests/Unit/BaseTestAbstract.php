<?php
namespace RemotelyLiving\PHPDNS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RemotelyLiving\PHPDNS\Entities\Interfaces\Arrayable;
use RemotelyLiving\PHPDNS\Entities\Interfaces\Serializable;

abstract class BaseTestAbstract extends TestCase
{
    protected function assertSerializable(Serializable $serializable)
    {
        $this->assertEquals($serializable, \unserialize(\serialize($serializable)));
    }

    protected function assertArrayableAndEquals(array $expected, Arrayable $arrayable)
    {
        $this->assertEquals($expected, $arrayable->toArray());
    }

    protected function assertStringableAndEquals(string $expected, string $stringable)
    {
        $this->assertSame($expected, $stringable);
    }
}
