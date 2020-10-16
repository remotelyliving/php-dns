<?php

namespace RemotelyLiving\PHPDNS\Entities;

use RemotelyLiving\PHPDNS\Entities\Interfaces\DNSRecordInterface;

use function serialize;
use function unserialize;

final class DNSRecord extends EntityAbstract implements DNSRecordInterface
{
    private DNSRecordType $recordType;

    private Hostname $hostname;

    private int $TTL;

    private ?IPAddress $IPAddress;

    private string $class;

    private ?DataAbstract $data;
    /**
     * @var string
     */
    private const DATA = 'data';

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
    ): DNSRecord {
        $type = DNSRecordType::createFromString($recordType);
        $hostname = Hostname::createFromString($hostname);
        $data = ($data !== null)
            ? DataAbstract::createFromTypeAndString($type, $data)
            : null;

        return new self(
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

    public function setTTL(int $ttl): self
    {
        $this->TTL = $ttl;
        return $this;
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
            $formatted[self::DATA] = (string)$this->data;
        }

        return $formatted;
    }

    public function equals(DNSRecordInterface $record): bool
    {
        return $this->hostname->equals($record->getHostname())
            && $this->recordType->equals($record->getType())
            && (string)$this->data === (string)$record->getData() // could be null
            && (string)$this->IPAddress === (string)$record->getIPAddress(); // could be null
    }

    public function serialize(): string
    {
        return serialize($this->toArray());
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized): void
    {
        $unserialized = unserialize($serialized);

        $rawIPAddres = $unserialized['IPAddress'] ?? null;
        $this->recordType = DNSRecordType::createFromString($unserialized['type']);
        $this->hostname = Hostname::createFromString($unserialized['hostname']);
        $this->TTL = (int) $unserialized['TTL'];
        $this->IPAddress = $rawIPAddres ? IPAddress::createFromString($rawIPAddres) : null;
        $this->class = $unserialized['class'];
        $this->data = (isset($unserialized[self::DATA]))
         ? DataAbstract::createFromTypeAndString($this->recordType, $unserialized[self::DATA])
         : null;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
