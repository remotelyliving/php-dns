<?php

namespace RemotelyLiving\PHPDNS\Tests\Unit\Entities;

use Countable;
use Iterator;
use RemotelyLiving\PHPDNS\Entities\DNSRecord;
use RemotelyLiving\PHPDNS\Entities\DNSRecordCollection;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Interfaces\Arrayable;
use RemotelyLiving\PHPDNS\Exceptions\InvalidArgumentException;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;
use Traversable;

class DNSRecordCollectionTest extends BaseTestAbstract
{
    private \RemotelyLiving\PHPDNS\Entities\DNSRecord $dnsRecord1;

    private \RemotelyLiving\PHPDNS\Entities\DNSRecord $dnsRecord2;

    private \RemotelyLiving\PHPDNS\Entities\DNSRecordCollection $dnsRecordCollection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dnsRecord1 = DNSRecord::createFromPrimitives(
            'A',
            'google.com',
            123,
            '127.0.0.1',
            'AS'
        );

        $this->dnsRecord2 = DNSRecord::createFromPrimitives(
            'CNAME',
            'google.com',
            123,
            '127.0.0.1',
            'IN'
        );

        $this->dnsRecordCollection = new DNSRecordCollection($this->dnsRecord1, $this->dnsRecord2);
    }

    /**
     * @test
     */
    public function isTraversable(): void
    {
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function isSerializable(): void
    {
        $this->assertSerializable($this->dnsRecordCollection);
    }

    /**
     * @test
     */
    public function picksFirst(): void
    {
        $this->assertEquals($this->dnsRecord1, $this->dnsRecordCollection->pickFirst());
        $this->assertEquals($this->dnsRecord1, $this->dnsRecordCollection->pickFirst());
        $this->assertSame(0, $this->dnsRecordCollection->key());
    }

    /**
     * @test
     */
    public function filtersByType(): void
    {
        $filtered = $this->dnsRecordCollection->filteredByType(DNSRecordType::createCNAME());
        $this->assertEquals($this->dnsRecord2, iterator_to_array($filtered)[0]);

        $nothing = $this->dnsRecordCollection->filteredByType(DNSRecordType::createTXT());

        $this->assertFalse($nothing->valid());
    }

    /**
     * @test
     */
    public function hasARecord(): void
    {
        $notInCollection = DNSRecord::createFromPrimitives('A', 'facebook.com', 3434);

        $this->assertFalse($this->dnsRecordCollection->has($notInCollection));
        $this->assertTrue($this->dnsRecordCollection->has($this->dnsRecord1));
        $this->assertTrue($this->dnsRecordCollection->has($this->dnsRecord2));
    }

    /**
     * @test
     */
    public function isCountableTraversableIteratable(): void
    {
        $this->assertInstanceOf(Traversable::class, $this->dnsRecordCollection);
        $this->assertInstanceOf(Countable::class, $this->dnsRecordCollection);
        $this->assertInstanceOf(Iterator::class, $this->dnsRecordCollection);

        foreach ($this->dnsRecordCollection as $record) {
            $this->assertInstanceOf(DNSRecord::class, $record);
        }

        $this->assertFalse(isset($this->dnsRecordCollection[2]));

        $this->assertFalse($this->dnsRecordCollection->isEmpty());

        $this->dnsRecordCollection[2] = DNSRecord::createFromPrimitives('A', 'facebook.com', 3434);

        $this->assertSame(3, count($this->dnsRecordCollection));

        $this->assertEquals($this->dnsRecord1, $this->dnsRecordCollection->offsetGet(0));

        unset($this->dnsRecordCollection[0], $this->dnsRecordCollection[1], $this->dnsRecordCollection[2]);

        $this->assertTrue($this->dnsRecordCollection->isEmpty());

        $this->dnsRecordCollection->offsetSet(0, DNSRecord::createFromPrimitives('A', 'facebook.com', 3434));

        $this->assertEquals(
            DNSRecord::createFromPrimitives('A', 'facebook.com', 3434),
            $this->dnsRecordCollection->offsetGet(0)
        );

        $this->assertFalse((bool)$this->dnsRecordCollection->key());
    }

    public function testOnlyAllowsDNSRecordsToBeSet(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->dnsRecordCollection[0] = 'boop';
    }

    /**
     * @test
     */
    public function isArrayable(): void
    {
        $this->assertArrayableAndEquals([$this->dnsRecord1, $this->dnsRecord2], $this->dnsRecordCollection);
    }

    /**
     * @test
     */
    public function isJsonSerializable(): void
    {
        $this->assertJsonSerializeableAndEquals([$this->dnsRecord1, $this->dnsRecord2], $this->dnsRecordCollection);
    }

    /**
     * @test
     */
    public function hasFilterMethods(): void
    {
        $expectedDupes = new DNSRecordCollection($this->dnsRecord2);
        $expectedUniques = new DNSRecordCollection($this->dnsRecord1, $this->dnsRecord2);
        $expectedOnlyOneResult = new DNSRecordCollection($this->dnsRecord1);

        $hasDupes = new DNSRecordCollection($this->dnsRecord1, $this->dnsRecord2, $this->dnsRecord2, $this->dnsRecord2);
        $hasOneResult = new DNSRecordCollection($this->dnsRecord1);

        $this->assertEquals($expectedDupes, $hasDupes->withUniqueValuesExcluded());
        $this->assertEquals($expectedUniques, $hasDupes->withUniqueValues());

        $this->assertEquals($expectedOnlyOneResult, $hasOneResult->withUniqueValues());
        $this->assertEquals(new DNSRecordCollection(), $hasOneResult->withUniqueValuesExcluded());
    }
}
