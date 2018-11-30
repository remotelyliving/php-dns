<?php
namespace RemotelyLiving\PHPDNS\Tests\Unit\Entities;

use RemotelyLiving\PHPDNS\Entities\IPAddress;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

class IPAddressTest extends BaseTestAbstract
{
    /**
     * @var \RemotelyLiving\PHPDNS\Entities\IPAddress
     */
    private $IPAddress;

    protected function setUp()
    {
        parent::setUp();

        $this->IPAddress = new IPAddress('127.0.0.1');
    }

    /**
     * @test
     */
    public function hasBasicGettersAndIsStringy()
    {
        $this->assertSame('127.0.0.1', (string)$this->IPAddress);
        $this->assertSame('127.0.0.1', $this->IPAddress->getIPAddress());
    }

    /**
     * @test
     */
    public function testsForEquality()
    {
        $IPAddress1 = IPAddress::createFromString('127.0.0.1');
        $IPAddress2 = IPAddress::createFromString('192.168.1.1');
        $IPAddress3 = IPAddress::createFromString('127.0.0.1');

        $this->assertFalse($IPAddress1->equals($IPAddress2));
        $this->assertTrue($IPAddress3->equals($IPAddress1));
    }

    /**
     * @test
     * @expectedException \RemotelyLiving\PHPDNS\Exceptions\InvalidArgumentException
     */
    public function doesNotAllowInvalidHostNames()
    {
        $this->assertFalse(IPAddress::isValid('lskjf'));
        IPAddress::createFromString('127.1.1');
    }

    /**
     * @test
     */
    public function knowsWhatVersionItIs()
    {
        $IPv4 = IPAddress::createFromString('127.0.0.1');
        $IPv6 = IPAddress::createFromString('2001:0db8:85a3:0000:0000:8a2e:0370:7334');

        $this->assertTrue($IPv4->isIPv4());
        $this->assertFalse($IPv4->isIPv6());
        $this->assertTrue($IPv6->isIPv6());
        $this->assertFalse($IPv6->isIPv4());
    }
}
