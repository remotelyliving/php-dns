<?php
namespace RemotelyLiving\PHPDNS\Tests\Integration;

use RemotelyLiving\PHPDNS\Entities\DNSRecordCollection;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Entities\IPAddress;
use RemotelyLiving\PHPDNS\Resolvers\Interfaces\ReverseDNSQuery;
use RemotelyLiving\PHPDNS\Resolvers\ResolverAbstract;

class ResolversTest extends BaseTestAbstract
{
    /**
     * @var \RemotelyLiving\PHPDNS\Entities\Hostname
     */
    private $hostname;

    protected function setUp()
    {
        parent::setUp();

        $this->hostname = Hostname::createFromString('facebook.com');
    }

    /**
     * @test
     * @dataProvider resolverProvider
     */
    public function getsRecords(ResolverAbstract $resolver)
    {
        foreach (DNSRecordType::VALID_TYPES as $type) {
            $collection = $resolver->getRecords($this->hostname, DNSRecordType::createFromString($type));
            $this->assertInstanceOf(DNSRecordCollection::class, $collection);

            if (!$collection->isEmpty()) {
                do {
                    $hasRecord = $resolver->hasRecord($collection->pickFirst());
                } while ($hasRecord === false);

                $this->assertTrue($hasRecord);
            }
        }
    }

    /**
     * @test
     */
    public function getsHostnameFromIpAddress()
    {
        $expectedHostname = Hostname::createFromString('localhost');
        $resolver = $this->createLocalSystemResolver();
        $this->assertInstanceOf(ReverseDNSQuery::class, $resolver);

        $hostname = $resolver->getHostnameByAddress(IPAddress::createFromString('127.0.0.1'));
        $this->assertTrue($expectedHostname->equals($hostname));
    }

    /**
     * @test
     * @expectedException \RemotelyLiving\PHPDNS\Resolvers\Exceptions\ReverseLookupFailure
     */
    public function throwsOnReverseLookupFailure()
    {
        $this->createLocalSystemResolver()->getHostnameByAddress(IPAddress::createFromString('41.1.1.14'));
    }

    /**
     * @test
     */
    public function cachesDNSLookups()
    {
        $cache = $this->createCachePool();
        $cache->clear();

        $resolver = $this->createCachedResolver($cache);

        $recordsNotCached = $resolver->getARecords(Hostname::createFromString('facebook.com'));
        $recordsCached = $resolver->getARecords(Hostname::createFromString('facebook.com'));

        $resolver->getARecords(Hostname::createFromString('aksjflksjdf.lksjf'));

        $this->assertEquals($recordsNotCached, $recordsCached);
    }

    public function resolverProvider(): array
    {
        return [
            'google dns resolver' => [$this->createGoogleDNSResolver()],
            'local system resolver' => [$this->createLocalSystemResolver()],
            'cloud flare resolver' => [$this->createCloudFlareResolver()],
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
