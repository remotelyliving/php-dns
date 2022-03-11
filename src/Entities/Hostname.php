<?php

namespace RemotelyLiving\PHPDNS\Entities;

use RemotelyLiving\PHPDNS\Exceptions\InvalidArgumentException;

use function filter_var;
use function idn_to_ascii;
use function idn_to_utf8;
use function mb_strtolower;
use function substr;
use function trim;

final class Hostname extends EntityAbstract implements \Stringable
{
    private string $hostname;

    /**
     * @throws \RemotelyLiving\PHPDNS\Exceptions\InvalidArgumentException
     */
    public function __construct(string $hostname)
    {
        $hostname = $this->normalizeHostName($hostname);

        if (filter_var($hostname, FILTER_VALIDATE_DOMAIN) !== $hostname) {
            throw new InvalidArgumentException("{$hostname} is not a valid hostname");
        }

        $this->hostname = $hostname;
    }

    public function __toString(): string
    {
        return $this->hostname;
    }

    public function equals(Hostname $hostname): bool
    {
        return $this->hostname === (string)$hostname;
    }

    public static function createFromString(string $hostname): Hostname
    {
        return new self($hostname);
    }

    public function getHostName(): string
    {
        return $this->hostname;
    }

    public function getHostnameWithoutTrailingDot(): string
    {
        return substr($this->hostname, 0, -1);
    }

    public function isPunycoded(): bool
    {
        return $this->toUTF8() !== $this->hostname;
    }

    public function toUTF8(): string
    {
        return (string)idn_to_utf8($this->hostname, IDNA_ERROR_PUNYCODE, INTL_IDNA_VARIANT_UTS46);
    }

    private static function punyCode(string $hostname): string
    {
        return (string)idn_to_ascii($hostname, IDNA_ERROR_PUNYCODE, INTL_IDNA_VARIANT_UTS46);
    }

    private function normalizeHostName(string $hostname): string
    {
        $hostname = self::punyCode(mb_strtolower(trim($hostname)));

        if (!str_ends_with($hostname, '.')) {
            return "{$hostname}.";
        }

        return $hostname;
    }
}
