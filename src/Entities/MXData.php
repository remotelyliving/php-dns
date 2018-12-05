<?php
namespace RemotelyLiving\PHPDNS\Entities;

class MXData extends DataAbstract
{
    /**
     * @var \RemotelyLiving\PHPDNS\Entities\Hostname
     */
    private $target;

    /**
     * @var int
     */
    private $priority;

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
        return \serialize($this->toArray());
    }

    public function unserialize($serialized): void
    {
        $unserialized = \unserialize($serialized);
        $this->target = new Hostname($unserialized['target']);
        $this->priority = $unserialized['priority'];
    }
}
