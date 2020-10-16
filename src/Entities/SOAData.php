<?php

namespace RemotelyLiving\PHPDNS\Entities;

use function serialize;
use function unserialize;
use function vsprintf;

final class SOAData extends DataAbstract
{
    private Hostname $mname;

    private Hostname $rname;

    private int $serial;

    private int $refresh;

    private int $retry;

    private int $expire;

    private int $minTTL;
    /**
     * @var string
     */
    private const TEMPLATE = '%s %s %s %s %s %s %s';

    public function __construct(
        Hostname $mname,
        Hostname $rname,
        int $serial,
        int $refresh,
        int $retry,
        int $expire,
        int $minTTL
    ) {
        $this->mname = $mname;
        $this->rname = $rname;
        $this->serial = $serial;
        $this->refresh = $refresh;
        $this->retry = $retry;
        $this->expire = $expire;
        $this->minTTL = $minTTL;
    }

    public function __toString(): string
    {
        return vsprintf(self::TEMPLATE, $this->toArray());
    }

    /**
     * @return \RemotelyLiving\PHPDNS\Entities\Hostname
     */
    public function getMname(): Hostname
    {
        return $this->mname;
    }

    /**
     * @return \RemotelyLiving\PHPDNS\Entities\Hostname
     */
    public function getRname(): Hostname
    {
        return $this->rname;
    }

    /**
     * @return int
     */
    public function getSerial(): int
    {
        return $this->serial;
    }

    /**
     * @return int
     */
    public function getRefresh(): int
    {
        return $this->refresh;
    }

    /**
     * @return int
     */
    public function getRetry(): int
    {
        return $this->retry;
    }

    /**
     * @return int
     */
    public function getExpire(): int
    {
        return $this->expire;
    }

    /**
     * @return int
     */
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

    public function serialize(): string
    {
        return serialize($this->toArray());
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized): void
    {
        $unserialized = unserialize($serialized);
        $this->mname = new Hostname($unserialized['mname']);
        $this->rname = new Hostname($unserialized['rname']);
        $this->serial = $unserialized['serial'];
        $this->refresh = $unserialized['refresh'];
        $this->retry = $unserialized['retry'];
        $this->expire = $unserialized['expire'];
        $this->minTTL = $unserialized['minimumTTL'];
    }
}
