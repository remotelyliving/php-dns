<?php
namespace RemotelyLiving\PHPDNS\Entities;

class NSData extends DataAbstract
{
    /**
     * @var string
     */
    private $target;

    public function __construct(string $target)
    {
        $this->target = $target;
    }

    public function __toString(): string
    {
        return $this->target;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function toArray(): array
    {
        return [
            'target' => $this->target,
        ];
    }

    public function serialize(): string
    {
        return \serialize($this->toArray());
    }

    public function unserialize($serialized): void
    {
        $this->target = $serialized['target'];
    }
}
