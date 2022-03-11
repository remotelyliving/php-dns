<?php

namespace RemotelyLiving\PHPDNS\Entities;

use function serialize;
use function unserialize;
use function vsprintf;

final class SOAData extends DataAbstract implements \Stringable
{
    /**
     * @var string
     */
    private const TEMPLATE = '%s %s %s %s %s %s %s';

    public function __construct(
        private Hostname $mname,
        private Hostname $rname,
        private int $serial,
        private int $refresh,
        private int $retry,
        private int $expire,
        private int $minTTL
    ) {
    }

    public function __toString(): string
    {
        return vsprintf(self::TEMPLATE, $this->toArray());
    }

    public function getMname(): Hostname
    {
        return $this->mname;
    }

    public function getRname(): Hostname
    {
        return $this->rname;
    }

    public function getSerial(): int
    {
        return $this->serial;
    }

    public function getRefresh(): int
    {
        return $this->refresh;
    }

    public function getRetry(): int
    {
        return $this->retry;
    }

    public function getExpire(): int
    {
        return $this->expire;
    }

    public function getMinTTL(): int
    {
        return $this->minTTL;
    }


    public function toArray(): array
    {
        return [
            'mname' => (string)$this->mname,
            'rname' => (string)$this->rname,
            'serial' => $this->serial,
            'refresh' => $this->refresh,
            'retry' => $this->retry,
            'expire' => $this->expire,
            'minimumTTL' => $this->minTTL,
        ];
    }

    public function __unserialize(array $unserialized): void
    {
        $this->mname = new Hostname($unserialized['mname']);
        $this->rname = new Hostname($unserialized['rname']);
        $this->serial = $unserialized['serial'];
        $this->refresh = $unserialized['refresh'];
        $this->retry = $unserialized['retry'];
        $this->expire = $unserialized['expire'];
        $this->minTTL = $unserialized['minimumTTL'];
    }
}
