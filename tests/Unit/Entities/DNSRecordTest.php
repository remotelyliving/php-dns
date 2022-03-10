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
    private \RemotelyLiving\PHPDNS\Entities\DNSRecord $DNSARecord;

    private \RemotelyLiving\PHPDNS\Entities\DNSRecord $DNSTXTRecord;

    protected function setUp(): void
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
    public function hasBasicGetters(): void
    {
        $this->assertSame(123, $this->DNSARecord->getTTL());
        $this->assertTrue(IPAddress::createFromString('127.0.0.1')->equals($this->DNSARecord->getIPAddress()));
        $this->assertTrue(Hostname::createFromString('google.com')->equals($this->DNSARecord->getHostname()));
        $this->assertTrue(DNSRecordType::createA()->equals($this->DNSARecord->getType()));
        $this->assertSame('AS', $this->DNSARecord->getClass());
        $this->assertNull($this->DNSARecord->getData());
    }

    public function hasBasicSetters(): void
    {
        $this->assertSame(123, $this->DNSARecord->getTTL());
        $this->assertSame(321, $this->DNSARecord->setTTL(321)->getTTL());
    }

    /**
     * @test
     */
    public function isArrayable(): void
    {
        $this->assertArrayableAndEquals([
            'hostname' => 'google.com.',
            'type' => 'A',
            'TTL' => 123,
            'class' => 'AS',
            'IPAddress' => '127.0.0.1',
        ], $this->DNSARecord);

        $this->assertArrayableAndEquals([
            'hostname' => 'google.com.',
            'type' => 'TXT',
            'TTL' => 123,
            'class' => 'AS',
            'data' => 'txtval'
        ], $this->DNSTXTRecord);
    }

    /**
     * @test
     */
    public function isJsonSerializable(): void
    {
        $this->assertJsonSerializeableAndEquals([
            'hostname' => 'google.com.',
            'type' => 'A',
            'TTL' => 123,
            'class' => 'AS',
            'IPAddress' => '127.0.0.1',
        ], $this->DNSARecord);

        $this->assertJsonSerializeableAndEquals([
            'hostname' => 'google.com.',
            'type' => 'TXT',
            'TTL' => 123,
            'class' => 'AS',
            'data' => 'txtval'
        ], $this->DNSTXTRecord);
    }

    /**
     * @test
     */
    public function isSerializable(): void
    {
        $this->assertSerializable($this->DNSARecord);

        $this->assertEquals(unserialize(serialize($this->DNSARecord)), $this->DNSARecord);
        $this->assertEquals(unserialize(serialize($this->DNSTXTRecord)), $this->DNSTXTRecord);
    }

    /**
     * @test
     */
    public function testsEquality(): void
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
