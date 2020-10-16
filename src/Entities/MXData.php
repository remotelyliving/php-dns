<?php

namespace RemotelyLiving\PHPDNS\Entities;

use function serialize;
use function unserialize;

final class MXData extends DataAbstract
{
    private Hostname $target;

    private int $priority;

    public function __construct(Hostname $target, int $priority = 0)
    {
        $this->target = $target;
        $this->priority = $priority;
    }

    public function __toString(): string
    {
        return "{$this->priority} {$this->target}";
    }

    public function getTarget(): Hostname
    {
        return $this->target;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function toArray(): array
    {
        return [
            'target' => (string)$this->target,
            'priority' => $this->priority,
        ];
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
        $this->target = new Hostname($unserialized['target']);
        $this->priority = $unserialized['priority'];
    }
}
