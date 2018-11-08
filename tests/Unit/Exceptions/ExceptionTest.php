<?php
namespace RemotelyLiving\PHPDNS\Tests\Unit\Exceptions;

use RemotelyLiving\PHPDNS\Exceptions\Exception;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

class ExceptionTest extends BaseTestAbstract
{
    /**
     * @test
     */
    public function isJsonSerializable()
    {
        $exception = new Exception('The exception', 123);

        $this->assertInstanceOf(\JsonSerializable::class, $exception);

        $jsonReady = $exception->jsonSerialize();

        $this->assertSame('The exception', $jsonReady['message']);
        $this->assertSame(123, $jsonReady['code']);
        $this->assertTrue(is_int($jsonReady['line']));
        $this->assertTrue(is_string($jsonReady['trace']));
    }
}
