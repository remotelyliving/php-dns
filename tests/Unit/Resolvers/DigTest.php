<?php

namespace RemotelyLiving\PHPDNS\Tests\Unit\Resolvers;

use RemotelyLiving\PHPDNS\Entities\DNSRecord;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Entities\IPAddress;
use RemotelyLiving\PHPDNS\Mappers\Dig as DigMapper;
use RemotelyLiving\PHPDNS\Mappers\MapperAbstract;
use RemotelyLiving\PHPDNS\Resolvers\Dig;
use RemotelyLiving\PHPDNS\Resolvers\Exceptions\QueryFailure;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;
use Spatie\Dns\Dns;
use Spatie\Dns\Records\A;
use Spatie\Dns\Records\Record;

class DigTest extends BaseTestAbstract
{
    private Dns $dig;
    private MapperAbstract $mapper;
    private Hostname $nameserver;
    private Hostname $hostname;
    private Record $record;

    private Dig $resolver;

    protected function setUp(): void
    {
        $this->dig = $this->createPartialMock(Dns::class, ['getRecords']);
        $this->mapper = new DigMapper();
        $this->nameserver = Hostname::createFromString('example.com');
        $this->hostname = Hostname::createFromString('facebook.com');
        $this->resolver = new Dig($this->dig, $this->mapper, $this->nameserver);
        $this->record = A::make([
            'host' => 'facebook.com',
            'ttl' => 123,
            'class' => 'IN',
            'type' => 'A',
            'ip' => '192.168.1.1'
        ]);

        $this->assertSame('example.com.', $this->dig->getNameserver());
    }

    public function testQuerysSupportedRecord(): void
    {

        $this->dig->method('getRecords')
            ->with('facebook.com.', 'A')
            ->willReturn([$this->record]);

        $expectedRecord = new DNSRecord(
            DNSRecordType::createA(),
            $this->hostname,
            123,
            IPAddress::createFromString('192.168.1.1')
        );

        $actual = $this->resolver->getRecords((string) $this->hostname, DNSRecordType::TYPE_A)[0];
        $this->assertTrue($expectedRecord->equals($actual));
    }

    public function testReturnsEmptyOnUnsupportedRecord(): void
    {

        $this->dig->expects($this->never())
            ->method('getRecords');

        $this->assertTrue($this->resolver->getRecords((string) $this->hostname, DNSRecordType::TYPE_PTR)->isEmpty());
    }

    public function testRecastsASpatieFailureAsQueryFailure(): void
    {

        $this->dig->method('getRecords')
            ->with('facebook.com.', 'A')
            ->willThrowException(new \DomainException('yo'));

        $this->expectException(QueryFailure::class);
        $this->expectExceptionMessage('yo');

        $this->resolver->getRecords((string) $this->hostname, DNSRecordType::TYPE_A);
    }
}
