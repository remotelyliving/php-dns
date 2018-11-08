<?php
namespace RemotelyLiving\PHPDNS\Tests\Unit\Entities;

use RemotelyLiving\PHPDNS\Entities\DNSRecord;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Entities\Interfaces\Arrayable;
use RemotelyLiving\PHPDNS\Entities\IPAddress;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

class DNSRecordTest extends BaseTestAbstract
{
    /**
     * @var \RemotelyLiving\PHPDNS\Entities\DNSRecord
     */
    private $DNSRecord;

    protected function setUp()
    {
        parent::setUp();

        $this->DNSRecord = DNSRecord::createFromPrimitives(
            'A',
            'google.com',
            123,
            '127.0.0.1',
            'AS'
        );
    }

    /**
     * @test
     */
    public function hasBasicGetters()
    {
        $this->assertSame(123, $this->DNSRecord->getTTL());
        $this->assertTrue(IPAddress::createFromString('127.0.0.1')->equals($this->DNSRecord->getIPAddress()));
        $this->assertTrue(Hostname::createFromString('google.com')->equals($this->DNSRecord->getHostname()));
        $this->assertTrue(DNSRecordType::createA()->equals($this->DNSRecord->getType()));
        $this->assertSame('AS', $this->DNSRecord->getClass());
    }

    /**
     * @test
     */
    public function isArrayable()
    {
        $this->assertInstanceOf(Arrayable::class, $this->DNSRecord);
        $this->assertEquals([
            'hostname' => 'google.com',
            'type' => 'A',
            'TTL' => 123,
            'class' => 'AS',
            'IPAddress' => '127.0.0.1',
        ], $this->DNSRecord->toArray());
    }

    /**
     * @test
     */
    public function isSerializable()
    {
        $this->assertSerializable($this->DNSRecord);
    }

    /**
     * @test
     */
    public function testsEquality()
    {
        $record2 = DNSRecord::createFromPrimitives(
            'A',
            'google.com',
            123,
            '192.168.1.1',
            'AS'
        );

        $record3 = DNSRecord::createFromPrimitives(
            'A',
            'google.com',
            321,
            '127.0.0.1',
            'AS'
        );

        $this->assertTrue($this->DNSRecord->equals($record3));
        $this->assertFalse($this->DNSRecord->equals($record2));
    }
}
