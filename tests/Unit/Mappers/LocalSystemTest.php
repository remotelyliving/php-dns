<?php
namespace RemotelyLiving\PHPDNS\Tests\Unit\Mappers;

use RemotelyLiving\PHPDNS\Entities\DNSRecord;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Mappers\LocalSystem;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

class LocalSystemTest extends BaseTestAbstract
{
    /**
     * @var \RemotelyLiving\PHPDNS\Mappers\LocalSystem
     */
    private $mapper;

    const LOCAL_DNS_FORMAT = [
        [
            'type' => 'AAAA',
            'ttl' => 343,
            'ipv6' => 'FE80:CD00:0000:0000:0000:0000:211E:729C',
            'host' => 'yelp.com',
            'class' => 'IN',
        ],
        [
            'type' => 'A',
            'ttl' => 343,
            'ip' => '127.0.0.1',
            'host' => 'google.com',
            'class' => 'IN',
        ],
        [
            'type' => 'CNAME',
            'ttl' => 343,
            'host' => 'google.com',
            'class' => 'IN'
        ],
        [
            'type' => 'NS',
            'ttl' => 343,
            'host' => 'google.com',
            'target' => 'ns.google.com.',
            'class' => 'IN'
        ],
        [
            'type' => 'SOA',
            'ttl' => 343,
            'host' => 'google.com',
            'mname' => 'ns.google.com.',
            'rname' => 'dns.google.com.',
            'serial' => 1234,
            'refresh' => 60,
            'retry' => 180,
            'expire' => 320,
            'minimum-ttl' => 84,
            'class' => 'IN'
        ],
        [
            'type' => 'MX',
            'ttl' => 343,
            'host' => 'google.com',
            'target' => 'ns.google.com.',
            'pri' => 60,
            'class' => 'IN',
        ],
        [
            'type' => 'TXT',
            'ttl' => 343,
            'host' => 'google.com',
            'txt' => 'txtval',
            'class' => 'IN',
        ],
        [
            'type' => 'CNAME',
            'class' => 'IN',
            'host' => 'www.google.com',
            'ttl' => 234,
            'target' => 'google.com',
        ],
        [
            'type' => 'CAA',
            'host' => 'thing.com',
            'class' => 'IN',
            'value' => 'google.com',
            'ttl' => 234,
            'tag' => 'issue',
            'flags' => 0
        ],
    ];

    protected function setUp()
    {
        parent::setUp();

        $this->mapper = new LocalSystem();
    }

    /**
     * @test
     */
    public function mapsAGoogleDNSRecordVariants()
    {
        $mappedRecord = $this->mapper->mapFields([
            'type' => 'A',
            'ttl' => 365,
            'ip' => '127.0.0.1',
            'host' => 'facebook.com',
            'class' => 'IN'
        ])->toDNSRecord();

        $this->assertEquals(
            DNSRecord::createFromPrimitives('A', 'facebook.com', 365, '127.0.0.1'),
            $mappedRecord
        );
    }

    /**
     * @test
     */
    public function mapsAllKindsOfLocalSystemDNSRecordVariants()
    {
        foreach (self::LOCAL_DNS_FORMAT as $record) {
            $mappedRecord = $this->mapper->mapFields($record)->toDNSRecord();
            $this->assertInstanceOf(DNSRecord::class, $mappedRecord);
        }
    }

    /**
     * @test
     */
    public function mapsRecordTypesToCorrespondingPHPConsts()
    {
        $this->assertEquals(32768, $this->mapper->getTypeCodeFromType(DNSRecordType::createTXT()));
    }
}
