<?php

namespace RemotelyLiving\PHPDNS\Tests\Integration;

use Psr\Log\NullLogger;
use RemotelyLiving\PHPDNS\Entities\CAAData;
use RemotelyLiving\PHPDNS\Entities\CNAMEData;
use RemotelyLiving\PHPDNS\Entities\DNSRecordCollection;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Entities\IPAddress;
use RemotelyLiving\PHPDNS\Entities\MXData;
use RemotelyLiving\PHPDNS\Entities\NSData;
use RemotelyLiving\PHPDNS\Entities\SOAData;
use RemotelyLiving\PHPDNS\Entities\TXTData;
use RemotelyLiving\PHPDNS\Resolvers\Exceptions\ReverseLookupFailure;
use RemotelyLiving\PHPDNS\Resolvers\Interfaces\ReverseDNSQuery;
use RemotelyLiving\PHPDNS\Resolvers\ResolverAbstract;

class ResolversTest extends BaseTestAbstract
{
    private const MAX_HAS_RECORD_WAIT_SECONDS = 3;

    /**
     * @var \RemotelyLiving\PHPDNS\Entities\Hostname
     */
    private $hostname;

    protected function setUp(): void
    {
        parent::setUp();

        $hostnames = [
            'google.com',
            'www.google.com',
            'facebook.com',
            'www.facebook.com',
            'wordpress.com',
            'www.wordpress.com'
        ];

        $this->hostname = Hostname::createFromString($hostnames[array_rand($hostnames)]);
    }

    /**
     * @test
     * @dataProvider resolverProvider
     */
    public function getsRecords(ResolverAbstract $resolver)
    {
        $resolver->setLogger(new NullLogger());

        foreach (DNSRecordType::VALID_TYPES as $type) {
            $collection = $resolver->getRecords($this->hostname, DNSRecordType::createFromString($type));
            $this->assertInstanceOf(DNSRecordCollection::class, $collection);
            $start = microtime(true);

            if (!$collection->isEmpty()) {
                do {
                    $now = microtime(true);
                    if ($now - $start >= self::MAX_HAS_RECORD_WAIT_SECONDS) {
                        $this->markTestIncomplete('Resolver took too long to determine if has record');
                        break;
                    }
                    $hasRecord = $resolver->hasRecord($collection->pickFirst());
                } while ($hasRecord === false);

                if ($collection[0]->getType()->equals(DNSRecordType::createTXT())) {
                    $this->assertInstanceOf(TXTData::class, $collection[0]->getData());
                }

                if ($collection[0]->getType()->equals(DNSRecordType::createNS())) {
                    $this->assertInstanceOf(NSData::class, $collection[0]->getData());
                }

                if ($collection[0]->getType()->equals(DNSRecordType::createMX())) {
                    $this->assertInstanceOf(MXData::class, $collection[0]->getData());
                }

                if ($collection[0]->getType()->equals(DNSRecordType::createSOA())) {
                    $this->assertInstanceOf(SOAData::class, $collection[0]->getData());
                }

                if ($collection[0]->getType()->equals(DNSRecordType::createCAA())) {
                    $this->assertInstanceOf(CAAData::class, $collection[0]->getData());
                }

                if ($collection[0]->getType()->equals(DNSRecordType::createCNAME())) {
                    $this->assertInstanceOf(CNAMEData::class, $collection[0]->getData());
                }

                if ($collection[0]->getType()->equals(DNSRecordType::createA())) {
                    $this->assertInstanceOf(IPAddress::class, $collection[0]->getIPAddress());
                    $this->assertTrue($collection[0]->getIPAddress()->isIPv4());
                }

                if ($collection[0]->getType()->equals(DNSRecordType::createAAAA())) {
                    $this->assertInstanceOf(IPAddress::class, $collection[0]->getIPAddress());
                    $this->assertTrue($collection[0]->getIPAddress()->isIPv6());
                }
            }
        }
    }

    /**
     * @test
     * @dataProvider resolverProvider
     */
    public function getsWWWRecords(ResolverAbstract $resolver)
    {
        $resolver->setLogger(new NullLogger());
        $wwwHostname = new Hostname('www.' . $this->hostname);

        foreach (['CAA'] as $type) {
            $collection = $resolver->getRecords($wwwHostname, DNSRecordType::createFromString($type));
            $this->assertInstanceOf(DNSRecordCollection::class, $collection);
            $start = microtime(true);

            if (!$collection->isEmpty()) {
                do {
                    $now = microtime(true);
                    if ($now - $start >= self::MAX_HAS_RECORD_WAIT_SECONDS) {
                        $this->markTestIncomplete('Resolver took too long to determine if has record');
                        break;
                    }
                    $hasRecord = $resolver->hasRecord($collection->pickFirst());
                } while ($hasRecord === false);

                if ($collection[0]->getType()->equals(DNSRecordType::createTXT())) {
                    $this->assertInstanceOf(TXTData::class, $collection[0]->getData());
                }

                if ($collection[0]->getType()->equals(DNSRecordType::createNS())) {
                    $this->assertInstanceOf(NSData::class, $collection[0]->getData());
                }

                if ($collection[0]->getType()->equals(DNSRecordType::createMX())) {
                    $this->assertInstanceOf(MXData::class, $collection[0]->getData());
                }

                if ($collection[0]->getType()->equals(DNSRecordType::createSOA())) {
                    $this->assertInstanceOf(SOAData::class, $collection[0]->getData());
                }

                if ($collection[0]->getType()->equals(DNSRecordType::createA())) {
                    $this->assertInstanceOf(IPAddress::class, $collection[0]->getIPAddress());
                    $this->assertTrue($collection[0]->getIPAddress()->isIPv4());
                }

                if ($collection[0]->getType()->equals(DNSRecordType::createAAAA())) {
                    $this->assertInstanceOf(IPAddress::class, $collection[0]->getIPAddress());
                    $this->assertTrue($collection[0]->getIPAddress()->isIPv6());
                }

                if ($collection[0]->getType()->equals(DNSRecordType::createCAA())) {
                    $this->assertInstanceOf(CAAData::class, $collection[0]->getData());
                }

                if ($collection[0]->getType()->equals(DNSRecordType::createCNAME())) {
                    $this->assertInstanceOf(CNAMEData::class, $collection[0]->getData());
                }
            }
        }
    }

    /**
     * @test
     */
    public function getsHostnameFromIpAddress(): void
    {
        $expectedHostname = Hostname::createFromString('localhost');
        $resolver = $this->createLocalSystemResolver();
        $this->assertInstanceOf(ReverseDNSQuery::class, $resolver);

        $hostname = $resolver->getHostnameByAddress(IPAddress::createFromString('127.0.0.1'));
        $this->assertTrue($expectedHostname->equals($hostname));
    }

    /**
     * @test
     */
    public function throwsOnReverseLookupFailure(): void
    {
        $this->expectException(ReverseLookupFailure::class);
        $this->createLocalSystemResolver()->getHostnameByAddress(IPAddress::createFromString('41.1.1.14'));
    }

    /**
     * @test
     */
    public function cachesDNSLookups(): void
    {
        $cache = $this->createCachePool();
        $cache->clear();

        $resolver = $this->createCachedResolver($cache);
        $resolver->flush();

        $cacheKey = md5('php-dns-v3:facebook.com.:A');

        $this->assertFalse($cache->getItem($cacheKey)->isHit());

        $recordsNotCached = $resolver->getARecords(Hostname::createFromString('facebook.com'));
        $recordsCached = $resolver->getARecords(Hostname::createFromString('facebook.com'));

        $resolver->getARecords(Hostname::createFromString('aksjflksjdf.lksjf'));

        $this->assertTrue($cache->getItem($cacheKey)->isHit());

        $this->assertSame(count($recordsNotCached), count($recordsCached));

        foreach ($recordsNotCached as $record) {
            $this->assertTrue($recordsCached->has($record));
        }
    }

    public function resolverProvider(): array
    {
        return [
            'google dns resolver' => [$this->createGoogleDNSResolver()],
            'local system resolver' => [$this->createLocalSystemResolver()],
            'cloud flare resolver' => [$this->createCloudFlareResolver()],
            'dig resolver' => [$this->createDigResolver()],
            'chain resolver with google first' => [
                $this->createChainResolver(
                    $this->createGoogleDNSResolver(),
                    $this->createCloudFlareResolver(),
                    $this->createLocalSystemResolver()
                )
            ],
            'chain resolver with local first' => [
                $this->createChainResolver($this->createLocalSystemResolver(), $this->createGoogleDNSResolver())
            ],
            'randomized chain resolver' => [
                $this->createChainResolver(
                    $this->createCloudFlareResolver(),
                    $this->createLocalSystemResolver(),
                    $this->createGoogleDNSResolver()
                )
            ],
        ];
    }
}
