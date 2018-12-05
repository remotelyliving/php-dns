<?php
namespace RemotelyLiving\PHPDNS\Entities;

use RemotelyLiving\PHPDNS\Entities\Interfaces\Arrayable;
use RemotelyLiving\PHPDNS\Entities\Interfaces\Serializable;

class DNSRecord extends EntityAbstract implements Arrayable, Serializable
{
    /**
     * @var \RemotelyLiving\PHPDNS\Entities\DNSRecordType
     */
    private $recordType;

    /**
     * @var \RemotelyLiving\PHPDNS\Entities\Hostname
     */
    private $hostname;

    /**
     * @var int
     */
    private $TTL;

    /**
     * @var \RemotelyLiving\PHPDNS\Entities\IPAddress|null
     */
    private $IPAddress;

    /**
     * @var string
     */
    private $class;

    /**
     * @var \RemotelyLiving\PHPDNS\Entities\DataAbstract|null
     */
    private $data;

    public function __construct(
        DNSRecordType $recordType,
        Hostname $hostname,
        int $ttl,
        IPAddress $IPAddress = null,
        string $class = 'IN',
        DataAbstract $data = null
    ) {
        $this->recordType = $recordType;
        $this->hostname = $hostname;
        $this->TTL = $ttl;
        $this->IPAddress = $IPAddress;
        $this->class = $class;
        $this->data = $data;
    }

    public static function createFromPrimitives(
        string $recordType,
        string $hostname,
        int $ttl,
        string $IPAddress = null,
        string $class = 'IN',
        string $data = null
    ) : DNSRecord {
        $type = DNSRecordType::createFromString($recordType);
        $hostname = Hostname::createFromString($hostname);
        $data = ($data !== null)
            ? DataAbstract::createFromTypeAndString($type, $data)
            : null;

        return new static(
            $type,
            $hostname,
            $ttl,
            $IPAddress ? IPAddress::createFromString($IPAddress) : null,
            $class,
            $data
        );
    }

    public function getType(): DNSRecordType
    {
        return $this->recordType;
    }

    public function getHostname(): Hostname
    {
        return $this->hostname;
    }

    public function getTTL(): int
    {
        return $this->TTL;
    }

    public function getIPAddress(): ?IPAddress
    {
        return $this->IPAddress;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getData(): ?DataAbstract
    {
        return $this->data;
    }

    public function toArray(): array
    {
        $formatted = [
            'hostname' => (string)$this->hostname,
            'type' => (string)$this->recordType,
            'TTL' => $this->TTL,
            'class' => $this->class,
        ];

        if ($this->IPAddress) {
            $formatted['IPAddress'] = (string)$this->IPAddress;
        }

        if ($this->data) {
            $formatted['data'] = (string)$this->data;
        }

        return $formatted;
    }

    public function equals(DNSRecord $record): bool
    {
        return $this->hostname->equals($record->getHostname())
            && $this->recordType->equals($record->getType())
            && (string)$this->data === (string)$record->getData() // could be null
            && (string)$this->IPAddress === (string)$record->getIPAddress(); // could be null
    }

    public function serialize(): string
    {
        return \serialize($this->toArray());
    }

    public function unserialize($record): void
    {
        $unserialized = \unserialize($record);

        $rawIPAddres = $unserialized['IPAddress'] ?? null;
        $this->recordType = DNSRecordType::createFromString($unserialized['type']);
        $this->hostname = Hostname::createFromString($unserialized['hostname']);
        $this->TTL = (int) $unserialized['TTL'];
        $this->IPAddress = $rawIPAddres ? IPAddress::createFromString($rawIPAddres) : null;
        $this->class = $unserialized['class'];
        $this->data = (isset($unserialized['data']))
         ? DataAbstract::createFromTypeAndString($this->recordType, $unserialized['data'])
         : null;
    }
}
