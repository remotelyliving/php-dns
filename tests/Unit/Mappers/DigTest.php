<?php

namespace RemotelyLiving\PHPDNS\Tests\Unit\Mappers;

use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Exceptions\InvalidArgumentException;
use RemotelyLiving\PHPDNS\Mappers\Dig;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

class DigTest extends BaseTestAbstract
{
    private Dig $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new Dig();
    }

    /**
     * @test
     */
    public function willNotMapUnsupportedType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->mapper->mapFields(['type' => 'PTR', 'host' => 'google.com', 'ttl' => 1, 'class' => 'IN'])->toDNSRecord();
    }

    /**
     * @dataProvider recordProvider
     * @test
     */
    public function mapsASpatieDigRecordToDNSRecord(array $fields): void
    {
        $type = DNSRecordType::createFromString($fields['type']);

        $this->assertTrue($type->equals($this->mapper->mapFields($fields)->toDNSRecord()->getType()));
    }

    public function recordProvider(): array
    {
        return [
            'A' => [['type' => 'A', 'host' => 'google.com', 'ttl' => 123, 'ip' => '192.168.1.1', 'class' => 'IN']],
            'AAAA' => [[
                'type' => 'AAAA',
                'host' => 'google.com',
                'ttl' => 123,
                'ip' => '2001:db8:3333:4444:5555:6666:7777:8888',
                'class' => 'IN'
            ]],
            'CAA' => [[
                'type' => 'CAA',
                'host' => 'google.com',
                'ttl' => 123,
                'flags' => 3,
                'class' => 'IN',
                'tag' => 'yo',
                'value' => 'gabbagabba',
            ]],
            'CNAME' => [[
                'type' => 'CNAME',
                'host' => 'google.com',
                'class' => 'IN',
                'ttl' => 123,
                'target' => 'www.google.com',
            ]],
            'MX' => [[
                'type' => 'MX',
                'host' => 'google.com',
                'ttl' => 123,
                'class' => 'IN',
                'target' => 'www.google.com',
                'pri' => 100,
            ]],
            'NS' => [[
                'type' => 'NS',
                'host' => 'google.com',
                'ttl' => 123,
                'class' => 'IN',
                'target' => 'www.namecheap.com',
            ]],
            'SOA' => [[
                'type' => 'SOA',
                'host' => 'google.com',
                'ttl' => 123,
                'class' => 'IN',
                'mname' => 'www.google.com',
                'rname' => 'google.com',
                'serial' => 234234,
                'refresh' => 1,
                'retry' => 2,
                'expire' => 3,
                'minimum_ttl' => 4,
            ]],
            'SRV' => [[
                'type' => 'SRV',
                'host' => 'google.com',
                'ttl' => 123,
                'class' => 'IN',
                'target' => 'www.google.com',
                'pri' => 100,
                'weight' => 4,
                'port' => 8080,
            ]],
            'TXT' => [[
                'type' => 'TXT',
                'host' => 'google.com',
                'ttl' => 123,
                'class' => 'IN',
                'txt' => 'to be or not to be',
            ]],
        ];
    }
}
