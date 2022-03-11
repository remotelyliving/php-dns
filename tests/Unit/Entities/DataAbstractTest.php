<?php

namespace RemotelyLiving\PHPDNS\Tests\Unit\Entities;

use RemotelyLiving\PHPDNS\Entities\DataAbstract;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Exceptions\InvalidArgumentException;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

class DataAbstractTest extends BaseTestAbstract
{
    private \RemotelyLiving\PHPDNS\Entities\DataAbstract $dataAbstract1;

    private \RemotelyLiving\PHPDNS\Entities\DataAbstract $dataAbstract2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dataAbstract1 = new class extends DataAbstract implements \Stringable {
            public function __toString(): string
            {
                return 'dataAbstract1';
            }

            public function toArray(): array
            {
                return [];
            }

            public function __serialize(): array
            {
                return ['seralized'];
            }

            public function __unserialize(array $serialized): void
            {
            }
        };

        $this->dataAbstract2 = new class extends DataAbstract implements \Stringable {
            public function __toString(): string
            {
                return 'dataAbstract2';
            }

            public function toArray(): array
            {
                return [];
            }

            public function __serialize(): array
            {
                return ['seralized'];
            }

            public function __unserialize(array $serialized): void
            {
            }
        };
    }

    /**
     * @test
     */
    public function knowsIfEquals(): void
    {
        $this->assertTrue($this->dataAbstract1->equals($this->dataAbstract1));
        $this->assertFalse($this->dataAbstract1->equals($this->dataAbstract2));
    }

    /**
     * @test
     */
    public function createsDataByType(): void
    {
        /** @var \RemotelyLiving\PHPDNS\Entities\TXTData $txtData */
        $txtData = $this->dataAbstract1::createFromTypeAndString(DNSRecordType::createTXT(), 'value');
        $this->assertSame('value', $txtData->getValue());

        /** @var \RemotelyLiving\PHPDNS\Entities\MXData $mxData */
        $mxData = $this->dataAbstract1::createFromTypeAndString(DNSRecordType::createMX(), '60 target.com');
        $this->assertSame('target.com.', (string)$mxData->getTarget());
        $this->assertSame(60, $mxData->getPriority());

        /** @var \RemotelyLiving\PHPDNS\Entities\NSData $nsData */
        $nsData = $this->dataAbstract1::createFromTypeAndString(DNSRecordType::createNS(), 'target.com');
        $this->assertSame('target.com.', (string)$nsData->getTarget());

        $soaString = 'ns1.google.com. dns-admin.google.com. 224049761 900 800 1800 60';
        $soaData = $this->dataAbstract1::createFromTypeAndString(DNSRecordType::createSOA(), $soaString);
        $this->assertSame('ns1.google.com.', (string)$soaData->getMname());
        $this->assertSame('dns-admin.google.com.', (string)$soaData->getRname());
        $this->assertSame(224_049_761, $soaData->getSerial());
        $this->assertSame(900, $soaData->getRefresh());
        $this->assertSame(800, $soaData->getRetry());
        $this->assertSame(1800, $soaData->getExpire());
        $this->assertSame(60, $soaData->getMinTTL());

        $cnameString = 'herp.website';
        $cnameData = $this->dataAbstract1::createFromTypeAndString(DNSRecordType::createCNAME(), $cnameString);
        $this->assertSame('herp.website.', (string)$cnameData->getHostname());

        $caaString = '0 issue "comodoca.com"';
        $caaData = $this->dataAbstract1::createFromTypeAndString(DNSRecordType::createCAA(), $caaString);
        $this->assertSame('comodoca.com', $caaData->getValue());
        $this->assertSame(0, $caaData->getFlags());
        $this->assertSame('issue', $caaData->getTag());

        $srvString = '100 200 9090 target.co.';
        $srvData = $this->dataAbstract1::createFromTypeAndString(DNSRecordType::createSRV(), $srvString);
        $this->assertSame(100, $srvData->getPriority());
        $this->assertSame(200, $srvData->getWeight());
        $this->assertSame(9090, $srvData->getPort());
        $this->assertSame('target.co.', (string)$srvData->getTarget());
    }

    /**
     * @test
     */
    public function createsDataByTypeOrThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->dataAbstract1::createFromTypeAndString(DNSRecordType::createA(), '');
    }

    /**
     * @test
     */
    public function checksCAADataAndThrowsIfTooManySegments(): void
    {
        $this->expectException(InvalidArgumentException::class);
        // example of bad data from Cloudflare API
        $invalid = '0 issue \\# 26 00 09 69 73 73 75 65 77 69 6c 64 6c 65 74 73 65 6e 63 72 79 70 74 2e 6f 72 67';
        $this->dataAbstract1::createFromTypeAndString(DNSRecordType::createCAA(), $invalid);
    }
}
