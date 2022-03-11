<?php

namespace RemotelyLiving\PHPDNS\Entities;

use function serialize;
use function unserialize;

final class MXData extends DataAbstract implements \Stringable
{
    public function __construct(private Hostname $target, private int $priority = 0)
    {
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

    public function __unserialize(array $unserialized): void
    {
        $this->target = new Hostname($unserialized['target']);
        $this->priority = $unserialized['priority'];
    }
}
