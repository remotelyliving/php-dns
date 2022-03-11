<?php

namespace RemotelyLiving\PHPDNS\Entities;

final class TXTData extends DataAbstract implements \Stringable
{
    public function __construct(private string $value)
    {
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

    public function __unserialize(array $unserialized): void
    {
        $this->value = $unserialized['value'];
    }
}
