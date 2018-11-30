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
            'type' => 'A', 'ttl' => 343, 'ipv6' => 'FE80:CD00:0000:0000:0000:0000:211E:729C', 'host' => 'yelp.com',
        ],
        [
            'type' => 'AAAA', 'ttl' => 343, 'ip' => '127.0.0.1', 'host' => 'google.com',
        ],
        [
            'type' => 'CNAME', 'ttl' => 343, 'host' => 'google.com',
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
