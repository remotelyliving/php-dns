<?php
namespace RemotelyLiving\PHPDNS\Tests\Unit\Mappers;

use RemotelyLiving\PHPDNS\Entities\DNSRecord;
use RemotelyLiving\PHPDNS\Mappers\GoogleDNS;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

class GoogleDNSTest extends BaseTestAbstract
{
    /**
     * @var \RemotelyLiving\PHPDNS\Mappers\GoogleDNS
     */
    private $mapper;

    const GOOGLE_DNS_FORMAT = [
        [
            'type' => 1, 'TTL' => 343, 'data' => '127.0.0.1', 'name' => 'yelp.com.',
        ],
        [
            'type' => 5, 'TTL' => 343, 'name' => 'google.com.',
        ],
        [
            'type' => 38, 'TTL' => 343, 'name' => 'google.com.',
        ],
    ];

    protected function setUp()
    {
        parent::setUp();

        $this->mapper = new GoogleDNS();
    }

    /**
     * @test
     */
    public function mapsAGoogleDNSRecordVariants()
    {
        $mappedRecord = $this->mapper->mapFields([
            'type' => 1,
            'TTL' => 365,
            'data' => '127.0.0.1',
            'name' => 'facebook.com.',
        ])->toDNSRecord();


        $this->assertEquals(
            DNSRecord::createFromPrimitives('A', 'facebook.com', 365, '127.0.0.1'),
            $mappedRecord
        );

        $mappedRecord = $this->mapper->mapFields([
            'type' => 16,
            'TTL' => 365,
            'name' => 'facebook.com.',
            'data' => '"txtval"',
        ])->toDNSRecord();

        $this->assertEquals(
            DNSRecord::createFromPrimitives('TXT', 'facebook.com', 365, null, 'IN', 'txtval'),
            $mappedRecord
        );
    }

    /**
     * @test
     */
    public function mapsAllKindsOfGoogleDNSRecordVariants()
    {
        foreach (self::GOOGLE_DNS_FORMAT as $record) {
            $mappedRecord = $this->mapper->mapFields($record)->toDNSRecord();
            $this->assertInstanceOf(DNSRecord::class, $mappedRecord);
        }
    }
}
