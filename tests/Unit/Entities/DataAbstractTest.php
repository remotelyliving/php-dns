<?php
namespace RemotelyLiving\PHPDNS\Tests\Unit\Entities;

use RemotelyLiving\PHPDNS\Entities\DataAbstract;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

class DataAbstractTest extends BaseTestAbstract
{
    /**
     * @var \RemotelyLiving\PHPDNS\Entities\DataAbstract
     */
    private $dataAbstract1;

    /**
     * @var \RemotelyLiving\PHPDNS\Entities\DataAbstract
     */
    private $dataAbstract2;

    protected function setUp()
    {
        parent::setUp();

        $this->dataAbstract1 = new class extends DataAbstract {
            public function __toString()
            {
                return 'dataAbstract1';
            }

            public function toArray(): array
            {
                return [];
            }

            public function serialize(): string
            {
                return 'seralized';
            }

            public function unserialize($serialized): void
            {
            }
        };

        $this->dataAbstract2 = new class extends DataAbstract {
            public function __toString()
            {
                return 'dataAbstract2';
            }

            public function toArray(): array
            {
                return [];
            }

            public function serialize(): string
            {
                return 'seralized';
            }

            public function unserialize($serialized): void
            {
            }
        };
    }

    /**
     * @test
     */
    public function knowsIfEquals()
    {
        $this->assertTrue($this->dataAbstract1->equals($this->dataAbstract1));
        $this->assertFalse($this->dataAbstract1->equals($this->dataAbstract2));
    }

    /**
     * @test
     */
    public function createsDataByType()
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

        /** @var \RemotelyLiving\PHPDNS\Entities\SOAData $soaData */
        $soaString = 'ns1.google.com. dns-admin.google.com. 224049761 900 800 1800 60';
        $soaData = $this->dataAbstract1::createFromTypeAndString(DNSRecordType::createSOA(), $soaString);
        $this->assertSame('ns1.google.com.', (string)$soaData->getMname());
        $this->assertSame('dns-admin.google.com.', (string)$soaData->getRname());
        $this->assertSame(224049761, $soaData->getSerial());
        $this->assertSame(900, $soaData->getRefresh());
        $this->assertSame(800, $soaData->getRetry());
        $this->assertSame(1800, $soaData->getExpire());
        $this->assertSame(60, $soaData->getMinTTL());

        /** @var \RemotelyLiving\PHPDNS\Entities\CNAMEData $cnameData */
        $cnameString = 'herp.website';
        $cnameData = $this->dataAbstract1::createFromTypeAndString(DNSRecordType::createCNAME(), $cnameString);
        $this->assertSame('herp.website.', (string)$cnameData->getHostname());

        /** @var \RemotelyLiving\PHPDNS\Entities\CAAData $caaData */
        $caaString = '0 issue "comodoca.com"';
        $caaData = $this->dataAbstract1::createFromTypeAndString(DNSRecordType::createCAA(), $caaString);
        $this->assertSame('comodoca.com', $caaData->getValue());
        $this->assertSame(0, $caaData->getFlags());
        $this->assertSame('issue', $caaData->getTag());
    }

    /**
     * @test
     * @expectedException \RemotelyLiving\PHPDNS\Exceptions\InvalidArgumentException
     */
    public function createsDataByTypeOrThrows()
    {
        $this->dataAbstract1::createFromTypeAndString(DNSRecordType::createA(), '');
    }
}
