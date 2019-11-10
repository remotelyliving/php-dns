<?php
namespace RemotelyLiving\PHPDNS\Tests\Unit\Resolvers;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use RemotelyLiving\PHPDNS\Entities\DNSRecord;
use RemotelyLiving\PHPDNS\Entities\DNSRecordCollection;
use RemotelyLiving\PHPDNS\Resolvers\Cached;
use RemotelyLiving\PHPDNS\Resolvers\Interfaces\Resolver;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

class CachedTest extends BaseTestAbstract
{
    /**
     * @var \DateTimeImmutable
     */
    private $dateTimeImmutable;

    /**
     * @var CacheItemPoolInterface;
     */
    private $cache;

    /**
     * @var Resolver
     */
    private $resolver;

    /**
     * @var \RemotelyLiving\PHPDNS\Resolvers\Cached
     */
    private $cachedResolver;

    /**
     * @var \RemotelyLiving\PHPDNS\Entities\DNSRecord
     */
    private $DNSRecord1;

    /**
     * @var \RemotelyLiving\PHPDNS\Entities\DNSRecord
     */
    private $DNSRecord2;

    /**
     * @var \RemotelyLiving\PHPDNS\Entities\DNSRecord
     */
    private $DNSRecord3;

    /**
     * @var \RemotelyLiving\PHPDNS\Entities\DNSRecordCollection
     */
    private $DNSRecordCollection;

    /**
     * @var CacheItemInterface
     */
    private $cacheItem;

    /**
     * @var int
     */
    private $timestamp;

    protected function setUp() : void
    {
        $this->timestamp = time();
        $this->dateTimeImmutable = $this->createMock(\DateTimeImmutable::class);
        $this->dateTimeImmutable->method('setTimeStamp')
            ->willReturn($this->dateTimeImmutable);
        $this->dateTimeImmutable->method('getTimeStamp')
            ->willReturnCallback(function () {
                return $this->timestamp;
            });

        $this->cacheItem = $this->createMock(CacheItemInterface::class);
        $this->cache = $this->createMock(CacheItemPoolInterface::class);
        $this->cache->method('getItem')
            ->with('8edc5a07804665f08fa769a9d760d479')
            ->willReturnCallback(function () {
                return $this->cacheItem;
            });

        $this->resolver = $this->createMock(Resolver::class);

        $this->DNSRecord1 = DNSRecord::createFromPrimitives('A', 'example.com', 100);
        $this->DNSRecord2 = DNSRecord::createFromPrimitives('AAAA', 'example.com', 200);
        $this->DNSRecord3 = DNSRecord::createFromPrimitives('MX', 'example.com', 300);
        $this->DNSRecordCollection = new DNSRecordCollection(...[
            $this->DNSRecord1,
            $this->DNSRecord2,
            $this->DNSRecord3
        ]);

        $this->resolver->method('getRecords')
            ->with('example.com.', 'ANY')
            ->willReturn($this->DNSRecordCollection);

        $this->cachedResolver = new Cached($this->cache, $this->resolver);
        $this->cachedResolver->setDateTimeImmutable($this->dateTimeImmutable);
    }

    /**
     * @test
     */
    public function testCachesUsingLowestTTLOnReturnedRecordSet() : void
    {
        $this->cacheItem->method('isHit')
            ->willReturn(false);

        $this->cacheItem->expects($this->once())
            ->method('expiresAfter')
            ->with(100);

        $this->cacheItem->expects($this->once())
            ->method('set')
            ->with(['recordCollection' => $this->DNSRecordCollection, 'timestamp' => $this->timestamp]);

        $this->cache->expects($this->once())
            ->method('save')
            ->with($this->cacheItem);

        $this->assertEquals($this->DNSRecordCollection, $this->cachedResolver->getRecords('example.com', 'ANY'));
    }

    /**
     * @test
     */
    public function testOnHitReturnsCachedValuesAndAdjustsTTLBasedOnTimeElapsedSinceStorage() : void
    {
        $this->cacheItem->method('isHit')
            ->willReturn(true);

        $this->cacheItem->method('get')
            ->willReturn(['recordCollection' => $this->DNSRecordCollection, 'timestamp' => $this->timestamp - 10]);

        $this->cache->expects($this->never())
            ->method('save');

        $DNSRecord1 = DNSRecord::createFromPrimitives('A', 'example.com', 90);
        $DNSRecord2 = DNSRecord::createFromPrimitives('AAAA', 'example.com', 190);
        $DNSRecord3 = DNSRecord::createFromPrimitives('MX', 'example.com', 290);
        $expectedDNSRecordCollection = new DNSRecordCollection(...[
            $DNSRecord1, $DNSRecord2, $DNSRecord3
        ]);

        $this->assertEquals(
            $expectedDNSRecordCollection,
            $this->cachedResolver->getRecords('example.com', 'ANY')
        );
    }
}