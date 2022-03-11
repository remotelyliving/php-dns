<?php

namespace RemotelyLiving\PHPDNS\Entities;

use RemotelyLiving\PHPDNS\Exceptions;

use function preg_match;
use function serialize;
use function str_ireplace;
use function trim;
use function unserialize;

final class CAAData extends DataAbstract implements \Stringable
{
    private ?string $value;

    public function __construct(private int $flags, private string $tag, string $value = null)
    {
        $this->value = ($value)
            ? $this->normalizeValue($value)
            : null;
    }

    public function __toString(): string
    {
        return "{$this->flags} {$this->tag} \"{$this->value}\"";
    }

    public function __unserialize(array $unserialized): void
    {
        $this->flags = $unserialized['flags'];
        $this->tag = $unserialized['tag'];
        $this->value = $unserialized['value'];
    }

    public function getFlags(): int
    {
        return $this->flags;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function toArray(): array
    {
        return [
            'flags' => $this->flags,
            'tag' => $this->tag,
            'value' => $this->value,
        ];
    }

    private function normalizeValue(string $value): string
    {
        $normalized = trim(str_ireplace('"', '', $value));

        if (preg_match('/\s/m', $normalized)) {
            throw new Exceptions\InvalidArgumentException("$value is not a valid CAA value");
        }

        return $normalized;
    }
}
