<?php
namespace RemotelyLiving\PHPDNS\Resolvers;

use RemotelyLiving\PHPDNS\Entities\DNSRecord;
use RemotelyLiving\PHPDNS\Entities\DNSRecordCollection;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Observability\Interfaces\Observable;
use RemotelyLiving\PHPDNS\Resolvers\Exceptions\QueryFailure;
use RemotelyLiving\PHPDNS\Resolvers\Interfaces\Randomizable;
use RemotelyLiving\PHPDNS\Resolvers\Interfaces\Resolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Chain extends ResolverAbstract implements Randomizable
{
    /**
     * @var \RemotelyLiving\PHPDNS\Resolvers\Interfaces\Resolver[]
     */
    private $resolvers;

    public function __construct(array $resolvers = [])
    {
        foreach ($resolvers as $resolver) {
            $this->pushResolver($resolver);
        }
    }

    public function pushResolver(Resolver...$resolvers): void
    {
        foreach ($resolvers as $resolver) {
            $this->resolvers[] = $resolver;
        }
    }

    public function randomly(): Randomizable
    {
        $randomized = clone $this;
        shuffle($randomized->resolvers);

        return $randomized;
    }

    public function addSubscriber(EventSubscriberInterface $subscriber): void
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver instanceof Observable) {
                $resolver->addSubscriber($subscriber);
            }
        }
    }

    public function addListener(string $eventName, callable $listener, int $priority = 0): void
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver instanceof Observable) {
                $resolver->addListener($eventName, $listener, $priority);
            }
        }
    }

    public function hasRecord(DNSRecord $record): bool
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver->hasRecord($record)) {
                return true;
            }
        }

        return false;
    }

    protected function doQuery(Hostname $hostname, DNSRecordType $recordType): DNSRecordCollection
    {
        foreach ($this->resolvers as $resolver) {
            try {
                $records = $resolver->getRecords($hostname, $recordType);
            } catch (QueryFailure $e) {
                continue;
            }

            if (!$records->isEmpty()) {
                return $records;
            }
        }

        return new DNSRecordCollection();
    }
}
