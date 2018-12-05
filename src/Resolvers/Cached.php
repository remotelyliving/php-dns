<?php
namespace RemotelyLiving\PHPDNS\Resolvers;

use Psr\Cache\CacheItemPoolInterface;
use RemotelyLiving\PHPDNS\Entities\DNSRecordCollection;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Resolvers\Interfaces\Resolver;

class Cached extends ResolverAbstract
{
    protected const DEFAULT_CACHE_TTL = 300;
    private const CACHE_KEY_TEMPLATE = '%s:%s:%s';

    /**
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    private $cache;

    /**
     * @var \RemotelyLiving\PHPDNS\Resolvers\Interfaces\Resolver
     */
    private $resolver;

    /**
     * @var string
     */
    private $namespace = 'php-dns';

    /**
     * @var int|null
     */
    private $ttlSeconds;

    public function __construct(CacheItemPoolInterface $cache, Resolver $resolver, int $ttlSeconds = null)
    {
        $this->cache = $cache;
        $this->resolver = $resolver;
        $this->ttlSeconds = $ttlSeconds;
    }

    public function flush(): void
    {
        $this->cache->clear();
    }

    protected function doQuery(Hostname $hostname, DNSRecordType $recordType): DNSRecordCollection
    {
        $cachedResult = $this->cache->getItem($this->buildCacheKey($hostname, $recordType));

        if ($cachedResult->isHit()) {
            return $cachedResult->get();
        }

        $dnsRecords = $this->resolver->getRecords($hostname, $recordType);
        $ttlSeconds = $this->ttlSeconds ?? $this->extractAverageTTL($dnsRecords);
        $cachedResult->expiresAfter($ttlSeconds);
        $cachedResult->set($dnsRecords);
        $this->cache->save($cachedResult);

        return $dnsRecords;
    }

    private function buildCacheKey(Hostname $hostname, DNSRecordType $recordType): string
    {
        return md5(sprintf(self::CACHE_KEY_TEMPLATE, $this->namespace, (string) $hostname, (string) $recordType));
    }

    private function extractAverageTTL(DNSRecordCollection $recordCollection): int
    {
        $ttls = [];

        if ($recordCollection->isEmpty()) {
            return self::DEFAULT_CACHE_TTL;
        }

        /** @var \RemotelyLiving\PHPDNS\Entities\DNSRecord $record */
        foreach ($recordCollection as $record) {
            /** @scrutinizer ignore-call */
            $ttls[] = $record->getTTL();
        }

        return (int) array_sum($ttls) / $recordCollection->count();
    }
}
