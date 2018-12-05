<?php
namespace RemotelyLiving\PHPDNS\Entities;

class TXTData extends DataAbstract
{
    /**
     * @var string
     */
    private $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
        ];
    }

    public function serialize(): string
    {
        return \serialize($this->toArray());
    }

    public function unserialize($serialized): void
    {
        $unserialized = \unserialize($serialized);
        $this->value = $unserialized['value'];
    }
}
