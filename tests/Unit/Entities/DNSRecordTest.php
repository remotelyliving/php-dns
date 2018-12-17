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
    private $DNSARecord;

    /**
     * @var \RemotelyLiving\PHPDNS\Entities\DNSRecord
     */
    private $DNSTXTRecord;

    protected function setUp()
    {
        parent::setUp();

        $this->DNSARecord = DNSRecord::createFromPrimitives(
            'A',
            'google.com',
            123,
            '127.0.0.1',
            'AS'
        );

        $this->DNSTXTRecord = DNSRecord::createFromPrimitives(
            'TXT',
            'google.com',
            123,
            null,
            'AS',
            'txtval'
        );
    }

    /**
     * @test
     */
    public function hasBasicGetters()
    {
        $this->assertSame(123, $this->DNSARecord->getTTL());
        $this->assertTrue(IPAddress::createFromString('127.0.0.1')->equals($this->DNSARecord->getIPAddress()));
        $this->assertTrue(Hostname::createFromString('google.com')->equals($this->DNSARecord->getHostname()));
        $this->assertTrue(DNSRecordType::createA()->equals($this->DNSARecord->getType()));
        $this->assertSame('AS', $this->DNSARecord->getClass());
        $this->assertNull($this->DNSARecord->getData());
    }

    /**
     * @test
     */
    public function isArrayable()
    {
        $this->assertInstanceOf(Arrayable::class, $this->DNSARecord);
        $this->assertEquals([
            'hostname' => 'google.com.',
            'type' => 'A',
            'TTL' => 123,
            'class' => 'AS',
            'IPAddress' => '127.0.0.1',
        ], $this->DNSARecord->toArray());

        $this->assertEquals([
            'hostname' => 'google.com.',
            'type' => 'TXT',
            'TTL' => 123,
            'class' => 'AS',
            'data' => 'txtval'
        ], $this->DNSTXTRecord->toArray());
    }

    /**
     * @test
     */
    public function isSerializable()
    {
        $this->assertSerializable($this->DNSARecord);

        $this->assertEquals(unserialize(serialize($this->DNSARecord)), $this->DNSARecord);
        $this->assertEquals(unserialize(serialize($this->DNSTXTRecord)), $this->DNSTXTRecord);
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

        $record3 = DNSRecord::createFromPrimitives(
            'A',
            'google.com',
            321,
            '127.0.0.1',
            'AS'
        );

        $record4 = DNSRecord::createFromPrimitives(
            'TXT',
            'google.com',
            321,
            null,
            'AS',
            'txtval'
        );

        $this->assertTrue($this->DNSARecord->equals($record3));
        $this->assertFalse($this->DNSARecord->equals($record2));

        $this->assertTrue($this->DNSTXTRecord->equals($record4));
        $this->assertFalse($this->DNSTXTRecord->equals($record3));
    }
}
