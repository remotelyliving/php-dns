<?php
namespace RemotelyLiving\PHPDNS\Observability\Events;

use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Exceptions\Exception;
use RemotelyLiving\PHPDNS\Resolvers\Interfaces\Resolver;

class DNSQueryFailed extends ObservableEventAbstract
{
    public const NAME = 'dns.query.failed';

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
     * @var \RemotelyLiving\PHPDNS\Exceptions\Exception
     */
    private $error;

    public function __construct(Resolver $resolver, Hostname $hostname, DNSRecordType $recordType, Exception $error)
    {
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
