<?php

namespace RemotelyLiving\PHPDNS\Entities;

use function serialize;
use function unserialize;

final class CNAMEData extends DataAbstract implements \Stringable
{
    public function __construct(private Hostname $hostname)
    {
    }

    public function __toString(): string
    {
        return (string)$this->hostname;
    }

    public function getHostname(): Hostname
    {
        return $this->hostname;
    }

    public function toArray(): array
    {
        return [
            'hostname' => (string)$this->hostname,
        ];
    }

    public function __unserialize(array $unserialized): void
    {
        $this->hostname = new Hostname($unserialized['hostname']);
    }
}
