<?php
namespace RemotelyLiving\PHPDNS\Entities;

class NSData extends DataAbstract
{
    /**
     * @var \RemotelyLiving\PHPDNS\Entities\Hostname
     */
    private $target;

    public function __construct(Hostname $target)
    {
        $this->target = $target;
    }

    public function __toString(): string
    {
        return (string)$this->target;
    }

    public function getTarget(): Hostname
    {
        return $this->target;
    }

    public function toArray(): array
    {
        return [
            'target' => (string)$this->target,
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
    }
}
