<?php

namespace RemotelyLiving\PHPDNS\Observability\Events;

use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Exceptions\Exception;
use RemotelyLiving\PHPDNS\Resolvers\Interfaces\Resolver;

final class DNSQueryFailed extends ObservableEventAbstract
{
    public const NAME = 'dns.query.failed';

    private Resolver $resolver;

    private Hostname $hostname;

    private DNSRecordType $recordType;

    private Exception $error;

    public function __construct(Resolver $resolver, Hostname $hostname, DNSRecordType $recordType, Exception $error)
    {
        parent::__construct();

        $this->resolver = $resolver;
        $this->hostname = $hostname;
        $this->recordType = $recordType;
        $this->error = $error;
    }

    public function getResolver(): Resolver
    {
        return $this->resolver;
    }

    public function getHostName(): Hostname
    {
        return $this->hostname;
    }

    public function getRecordType(): DNSRecordType
    {
        return $this->recordType;
    }

    public function getError(): Exception
    {
        return $this->error;
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
            'error' => $this->error,
        ];
    }
}
