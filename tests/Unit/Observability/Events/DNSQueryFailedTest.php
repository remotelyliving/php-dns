<?php

namespace RemotelyLiving\PHPDNS\Tests\Unit\Observability\Events;

use JsonSerializable;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Entities\Interfaces\Arrayable;
use RemotelyLiving\PHPDNS\Exceptions\Exception;
use RemotelyLiving\PHPDNS\Observability\Events\DNSQueryFailed;
use RemotelyLiving\PHPDNS\Resolvers\Interfaces\Resolver;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

class DNSQueryFailedTest extends BaseTestAbstract
{
    private \RemotelyLiving\PHPDNS\Resolvers\Interfaces\Resolver $resolver;

    private \RemotelyLiving\PHPDNS\Entities\Hostname $hostname;

    private \RemotelyLiving\PHPDNS\Entities\DNSRecordType $recordType;

    private \RemotelyLiving\PHPDNS\Exceptions\Exception $error;

    private \RemotelyLiving\PHPDNS\Observability\Events\DNSQueryFailed $DNSQueryFailed;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = $this->createMock(Resolver::class);
        $this->resolver->method('getName')
            ->willReturn('foo');

        $this->hostname = Hostname::createFromString('facebook.com');
        $this->recordType = DNSRecordType::createTXT();
        $this->error = new Exception();

        $this->DNSQueryFailed = new DNSQueryFailed(
            $this->resolver,
            $this->hostname,
            $this->recordType,
            $this->error
        );
    }

    /**
     * @test
     */
    public function hasBasicGetters(): void
    {
        $this->assertSame($this->resolver, $this->DNSQueryFailed->getResolver());
        $this->assertSame($this->hostname, $this->DNSQueryFailed->getHostname());
        $this->assertSame($this->recordType, $this->DNSQueryFailed->getRecordType());
        $this->assertSame($this->error, $this->DNSQueryFailed->getError());
        $this->assertSame(DNSQueryFailed::NAME, $this->DNSQueryFailed::getName());
    }

    /**
     * @test
     */
    public function isArrayable(): void
    {
        $expected = [
            'resolver' => 'foo',
            'hostname' => (string)$this->hostname,
            'type' => (string)$this->recordType,
            'error' => $this->error,
        ];

        $this->assertInstanceOf(Arrayable::class, $this->DNSQueryFailed);
        $this->assertInstanceOf(JsonSerializable::class, $this->error);
        $this->assertEquals($expected, $this->DNSQueryFailed->toArray());
    }
}
