<?php

namespace RemotelyLiving\PHPDNS\Tests\Unit\Observability\Events;

use RemotelyLiving\PHPDNS\Entities\DNSRecord;
use RemotelyLiving\PHPDNS\Entities\DNSRecordCollection;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Entities\Interfaces\Arrayable;
use RemotelyLiving\PHPDNS\Entities\Interfaces\DNSRecordInterface;
use RemotelyLiving\PHPDNS\Observability\Events\DNSQueried;
use RemotelyLiving\PHPDNS\Resolvers\Interfaces\Resolver;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

class DNSQueriedTest extends BaseTestAbstract
{
    private \RemotelyLiving\PHPDNS\Resolvers\Interfaces\Resolver $resolver;

    private \RemotelyLiving\PHPDNS\Entities\Hostname $hostname;

    private \RemotelyLiving\PHPDNS\Entities\DNSRecordType $recordType;

    private \RemotelyLiving\PHPDNS\Entities\DNSRecordCollection $recordCollection;

    private \RemotelyLiving\PHPDNS\Observability\Events\DNSQueried $DNSQueried;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = $this->createMock(Resolver::class);
        $this->resolver->method('getName')
            ->willReturn('foo');

        $this->hostname = Hostname::createFromString('facebook.com');
        $this->recordType = DNSRecordType::createTXT();
        $this->recordCollection = new DNSRecordCollection();

        $this->DNSQueried = new DNSQueried(
            $this->resolver,
            $this->hostname,
            $this->recordType,
            $this->recordCollection
        );
    }

    /**
     * @test
     */
    public function hasBasicGetters(): void
    {
        $this->assertSame($this->resolver, $this->DNSQueried->getResolver());
        $this->assertSame($this->hostname, $this->DNSQueried->getHostname());
        $this->assertSame($this->recordType, $this->DNSQueried->getRecordType());
        $this->assertSame($this->recordCollection, $this->DNSQueried->getRecordCollection());
        $this->assertSame(DNSQueried::NAME, $this->DNSQueried::getName());
    }

    /**
     * @test
     */
    public function isArrayable(): void
    {
        $record1 = $this->createMock(DNSRecordInterface::class);
        $record1->method('toArray')
            ->willReturn(['herp' => 'derp']);

        $record2 = $this->createMock(DNSRecordInterface::class);
        $record2->method('toArray')
            ->willReturn(['beep' => 'boop']);

        $expected = [
            'resolver' => 'foo',
            'hostname' => (string)$this->hostname,
            'type' => (string)$this->recordType,
            'records' => [
                ['herp' => 'derp'],
                ['beep' => 'boop'],
            ],
            'empty' => false,
        ];

        $this->recordCollection[] = $record1;
        $this->recordCollection[] = $record2;

        $this->assertInstanceOf(Arrayable::class, $this->DNSQueried);
        $this->assertEquals($expected, $this->DNSQueried->toArray());
    }
}
