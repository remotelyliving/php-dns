<?php
namespace RemotelyLiving\PHPDNS\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use RemotelyLiving\PHPDNS\Observability\Subscribers\STDIOSubscriber;
use RemotelyLiving\PHPDNS\Resolvers\Cached;
use RemotelyLiving\PHPDNS\Resolvers\CloudFlare;
use RemotelyLiving\PHPDNS\Resolvers\Chain;
use RemotelyLiving\PHPDNS\Resolvers\GoogleDNS;
use RemotelyLiving\PHPDNS\Resolvers\Interfaces\Resolver;
use RemotelyLiving\PHPDNS\Resolvers\LocalSystem;
use RemotelyLiving\PHPDNS\Resolvers\ResolverAbstract;
use RemotelyLiving\PHPDNS\Services\LocalSystemDNS;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

abstract class BaseTestAbstract extends TestCase
{
    protected function createGoogleDNSResolver(): GoogleDNS
    {
        return new GoogleDNS();
    }

    protected function createCloudFlareResolver(): CloudFlare
    {
        return new CloudFlare();
    }

    protected function createLocalSystemResolver(): LocalSystem
    {
        return new LocalSystem();
    }

    protected function createLocalSystemDNS(): LocalSystemDNS
    {
        return new LocalSystemDNS();
    }

    protected function createChainResolver(Resolver...$resolvers): Chain
    {
        return new Chain(...$resolvers);
    }

    protected function createCachePool(): CacheItemPoolInterface
    {
        return new FilesystemAdapter();
    }

    protected function createCachedResolver(CacheItemPoolInterface $cachePool): Cached
    {
        return new Cached($cachePool, $this->createLocalSystemResolver());
    }

    protected function createStdIOSubscriber(): EventSubscriberInterface
    {
        return new STDIOSubscriber(new \SplFileObject('php://stdout'), new \SplFileObject('php://stderr'));
    }

    protected function attachTestSubscribers(ResolverAbstract $resolver): void
    {
        $subscribers = [$this->createStdIOSubscriber()];

        foreach ($subscribers as $subscriber) {
            $resolver->addSubscriber($subscriber);
        }
    }
}
