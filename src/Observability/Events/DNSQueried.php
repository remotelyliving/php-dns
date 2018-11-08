<?php
namespace RemotelyLiving\PHPDNS\Observability\Events;

use RemotelyLiving\PHPDNS\Entities\DNSRecordCollection;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Resolvers\Interfaces\Resolver;

class DNSQueried extends ObservableEventAbstract
{
    public const NAME = 'dns.queried';

    /**
     * @var \RemotelyLiving\PHPDNS\Resolvers\Interfaces\Resolver
     */
    private $resolver;

    /**
     * @var \RemotelyLiving\PHPDNS\Entities\Hostname
     */
    private $hostname;

    /**
     * @var \RemotelyLiving\PHPDNS\Entities\DNSRecordType
     */
    private $recordType;

    /**
     * @var \RemotelyLiving\PHPDNS\Entities\DNSRecordCollection
     */
    private $recordCollection;

    public function __construct(
        Resolver $resolver,
        Hostname $hostname,
        DNSRecordType $recordType,
        DNSRecordCollection $recordCollection = null
    ) {
        $this->resolver = $resolver;
        $this->hostname = $hostname;
        $this->recordType = $recordType;
        $this->recordCollection = $recordCollection ?? new DNSRecordCollection();
    }

    public function getResolver(): Resolver
    {
        return $this->resolver;
    }

    public function getHostname(): Hostname
    {
        return $this->hostname;
    }

    public function getRecordType(): DNSRecordType
    {
        return $this->recordType;
    }

    public function getRecordCollection(): DNSRecordCollection
    {
        return $this->recordCollection;
    }

    public static function getName(): string
    {
        return self::NAME;
    }

    public function toArray(): array
    {
        return [
            'resolver' => $this->resolver->getName(),
            'hostname' => (string)$this->hostname,
            'type' => (string)$this->recordType,
            'records' => $this->formatCollection($this->recordCollection),
            'empty' => $this->recordCollection->isEmpty(),
        ];
    }

    private function formatCollection(DNSRecordCollection $recordCollection): array
    {
        $formatted = [];

        foreach ($recordCollection as $record) {
            if ($record) {
                $formatted[] = $record->toArray();
            }
        }

        return $formatted;
    }
}
