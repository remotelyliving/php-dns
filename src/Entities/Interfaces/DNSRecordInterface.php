<?php

namespace RemotelyLiving\PHPDNS\Entities\Interfaces;

use RemotelyLiving\PHPDNS\Entities\DataAbstract;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Entities\IPAddress;

interface DNSRecordInterface extends Arrayable, Serializable
{
    public function getType(): DNSRecordType;

    public function getHostname(): Hostname;

    public function getTTL(): int;

    public function getIPAddress(): ?IPAddress;

    public function getClass(): string;

    public function getData(): ?DataAbstract;

    public function setTTL(int $ttl): DNSRecordInterface;

    public function toArray(): array;

    public function equals(DNSRecordInterface $record): bool;
}
