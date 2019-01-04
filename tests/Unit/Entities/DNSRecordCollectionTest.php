<?php
namespace RemotelyLiving\PHPDNS\Tests\Unit\Entities;

use RemotelyLiving\PHPDNS\Entities\DNSRecord;
use RemotelyLiving\PHPDNS\Entities\DNSRecordCollection;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Interfaces\Arrayable;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

class DNSRecordCollectionTest extends BaseTestAbstract
{
    /**
     * @var \RemotelyLiving\PHPDNS\Entities\DNSRecord
     */
    private $dnsRecord1;

    /**
     * @var \RemotelyLiving\PHPDNS\Entities\DNSRecord
     */
    private $dnsRecord2;

    /**
     * @var \RemotelyLiving\PHPDNS\Entities\DNSRecordCollection
     */
    private $dnsRecordCollection;

    protected function setUp()
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
    public function isTraversable()
    {
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function isSerializable()
    {
        $this->assertSerializable($this->dnsRecordCollection);
    }

    /**
     * @test
     */
    public function picksFirst()
    {
        $this->assertEquals($this->dnsRecord1, $this->dnsRecordCollection->pickFirst());
        $this->assertEquals($this->dnsRecord1, $this->dnsRecordCollection->pickFirst());
        $this->assertSame(0, $this->dnsRecordCollection->key());
    }

    /**
     * @test
     */
    public function filtersByType()
    {
        $filtered = $this->dnsRecordCollection->filteredByType(DNSRecordType::createCNAME());
        $this->assertEquals($this->dnsRecord2, iterator_to_array($filtered)[0]);

        $nothing = $this->dnsRecordCollection->filteredByType(DNSRecordType::createTXT());

        $this->assertFalse($nothing->valid());
    }

    /**
     * @test
     */
    public function hasARecord()
    {
        $notInCollection = DNSRecord::createFromPrimitives('A', 'facebook.com', 3434);

        $this->assertFalse($this->dnsRecordCollection->has($notInCollection));
        $this->assertTrue($this->dnsRecordCollection->has($this->dnsRecord1));
        $this->assertTrue($this->dnsRecordCollection->has($this->dnsRecord2));
    }

    /**
     * @test
     */
    public function isCountableTraversableIteratable()
    {
        $this->assertInstanceOf(\Traversable::class, $this->dnsRecordCollection);
        $this->assertInstanceOf(\Countable::class, $this->dnsRecordCollection);
        $this->assertInstanceOf(\Iterator::class, $this->dnsRecordCollection);

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

    /**
     * @expectedException \RemotelyLiving\PHPDNS\Exceptions\InvalidArgumentException
     */
    public function testOnlyAllowsDNSRecordsToBeSet()
    {
        $this->dnsRecordCollection[0] = 'boop';
    }

    /**
     * @test
     */
    public function isArrayable()
    {
        $this->assertInstanceOf(Arrayable::class, $this->dnsRecordCollection);
        $this->assertEquals([$this->dnsRecord1, $this->dnsRecord2], $this->dnsRecordCollection->toArray());
    }

    /**
     * @test
     */
    public function hasFilterMethods()
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
