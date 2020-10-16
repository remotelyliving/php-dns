<?php

namespace RemotelyLiving\PHPDNS\Entities;

final class SRVData extends DataAbstract
{
    private int $priority;

    private int $weight;

    private int $port;

    private \RemotelyLiving\PHPDNS\Entities\Hostname $target;

    public function __construct(int $priority, int $weight, int $port, Hostname $target)
    {
        $this->priority = $priority;
        $this->weight = $weight;
        $this->port = $port;
        $this->target = $target;
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

    public function serialize(): string
    {
        return \serialize($this->toArray());
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized): void
    {
        $unserialized = \unserialize($serialized);
        $this->priority = $unserialized['priority'];
        $this->weight = $unserialized['weight'];
        $this->port = $unserialized['port'];
        $this->target = new Hostname($unserialized['target']);
    }
}
