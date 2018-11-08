<?php
namespace RemotelyLiving\PHPDNS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RemotelyLiving\PHPDNS\Entities\Interfaces\Serializable;

abstract class BaseTestAbstract extends TestCase
{
    public function assertSerializable(Serializable $serializable)
    {
        $this->assertEquals($serializable, \unserialize(\serialize($serializable)));
    }
}
