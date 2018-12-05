<?php
namespace RemotelyLiving\PHPDNS\Entities;

class MXData extends DataAbstract
{
    /**
     * @var string
     */
    private $target;

    /**
     * @var int
     */
    private $priority;

    public function __construct(string $target, int $priority = 0)
    {
        $this->target = $target;
        $this->priority = $priority;
    }

    public function __toString(): string
    {
        return "{$this->priority} {$this->target}";
    }

    public function getTarget(): string
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
            'target' => $this->target,
            'priority' => $this->priority,
        ];
    }

    public function serialize(): string
    {
        return \serialize($this->toArray());
    }

    public function unserialize($serialized): void
    {
        $this->target = $serialized['target'];
        $this->priority = $serialized['priority'];
    }
}
