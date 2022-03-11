<?php

namespace RemotelyLiving\PHPDNS\Entities;

use RemotelyLiving\PHPDNS\Entities\Interfaces\DNSRecordInterface;

use function serialize;
use function unserialize;

final class DNSRecord extends EntityAbstract implements DNSRecordInterface
{
    /**
     * @var string
     */
    private const DATA = 'data';

    public function __construct(
        private DNSRecordType $recordType,
        private Hostname $hostname,
        private int $TTL,
        private ?IPAddress $IPAddress = null,
        private string $class = 'IN',
        private ?DataAbstract $data = null
    ) {
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

    public function setData(DataAbstract $data): self
    {

        $this->data = $data;
        return $this;
    }

    public function setTTL(int $ttl): DNSRecordInterface
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

        if ($this->IPAddress !== null) {
            $formatted['IPAddress'] = (string)$this->IPAddress;
        }

        if ($this->data !== null) {
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

    public function __serialize(): array
    {
        return $this->toArray();
    }

    public function __unserialize(array $unserialized): void
    {
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
