<?php

namespace RemotelyLiving\PHPDNS\Entities;

use function serialize;
use function unserialize;

final class SRVData extends DataAbstract implements \Stringable
{
    public function __construct(private int $priority, private int $weight, private int $port, private Hostname $target)
    {
    }

    public function __toString(): string
    {
        return "{$this->priority} {$this->weight} {$this->port} {$this->target}";
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getTarget(): Hostname
    {
        return $this->target;
    }

    public function toArray(): array
    {
        return [
            'priority' => $this->priority,
            'weight'  => $this->weight,
            'port'    => $this->port,
            'target' => (string)$this->target,
        ];
    }

    public function __unserialize(array $unserialized): void
    {
        $this->priority = $unserialized['priority'];
        $this->weight = $unserialized['weight'];
        $this->port = $unserialized['port'];
        $this->target = new Hostname($unserialized['target']);
    }
}
