<?php

namespace RemotelyLiving\PHPDNS\Observability\Events;

use RemotelyLiving\PHPDNS\Entities\DNSRecordCollection;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Resolvers\Interfaces\Resolver;

final class DNSQueried extends ObservableEventAbstract
{
    public const NAME = 'dns.queried';

    private DNSRecordCollection $recordCollection;

    public function __construct(
        private Resolver $resolver,
        private Hostname $hostname,
        private DNSRecordType $recordType,
        DNSRecordCollection $recordCollection = null
    ) {
        parent::__construct();
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
            if ($record !== null) {
                $formatted[] = $record->toArray();
            }
        }

        return $formatted;
    }
}
