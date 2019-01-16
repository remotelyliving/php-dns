<?php
namespace RemotelyLiving\PHPDNS\Resolvers;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use RemotelyLiving\PHPDNS\Entities\DNSRecord;
use RemotelyLiving\PHPDNS\Entities\DNSRecordCollection;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Exceptions\Exception;
use RemotelyLiving\PHPDNS\Observability\Interfaces\Observable;
use RemotelyLiving\PHPDNS\Resolvers\Interfaces;
use RemotelyLiving\PHPDNS\Resolvers\Interfaces\Resolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Chain extends ResolverAbstract implements Interfaces\Chain
{
    const STRATEGY_FIRST_TO_FIND = 0;
    const STRATEGY_ALL_RESULTS = 1;
    const STRATEGY_CONSENSUS = 2;

    /**
     * @var \RemotelyLiving\PHPDNS\Resolvers\Interfaces\Resolver[]
     */
    private $resolvers;

    /**
     * @var int
     */
    private $callThroughStrategy = self::STRATEGY_FIRST_TO_FIND;

    public function __construct(Resolver...$resolvers)
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

    public function withAllResults(): Interfaces\Chain
    {
        $all = new self(...$this->resolvers);
        $all->callThroughStrategy = self::STRATEGY_ALL_RESULTS;

        return $all;
    }

    public function withFirstResults(): Interfaces\Chain
    {
        $first = new self(...$this->resolvers);
        $first->callThroughStrategy = self::STRATEGY_FIRST_TO_FIND;

        return $first;
    }

    public function withConsensusResults(): Interfaces\Chain
    {
        $consensus = new self(...$this->resolvers);
        $consensus->callThroughStrategy = self::STRATEGY_CONSENSUS;

        return $consensus;
    }

    public function randomly(): Interfaces\Chain
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

    public function setLogger(LoggerInterface $logger): void
    {
        foreach ($this->resolvers as $resolver) {
            if ($resolver instanceof LoggerAwareInterface) {
                $resolver->setLogger($logger);
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
        $merged = [];

        foreach ($this->resolvers as $resolver) {
            try {
                $records = $resolver->getRecords($hostname, $recordType);
            } catch (Exception $e) {
                continue;
            }

            if ($this->callThroughStrategy === self::STRATEGY_FIRST_TO_FIND && !$records->isEmpty()) {
                return $records;
            }

            /** @var DNSRecord $record */
            foreach ($records as $record) {
                $merged[] = $record;
            }
        }

        $collection = new DNSRecordCollection(...$merged);

        return ($this->callThroughStrategy === self::STRATEGY_CONSENSUS)
            ? $collection->withUniqueValuesExcluded()
            : $collection->withUniqueValues();
    }
}
