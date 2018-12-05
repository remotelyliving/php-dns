<?php
namespace RemotelyLiving\PHPDNS\Entities;

class SOAData extends DataAbstract
{
    /**
     * @var \RemotelyLiving\PHPDNS\Entities\Hostname
     */
    private $mname;

    /**
     * @var \RemotelyLiving\PHPDNS\Entities\Hostname
     */
    private $rname;

    /**
     * @var int
     */
    private $serial;

    /**
     * @var int
     */
    private $refresh;

    /**
     * @var int
     */
    private $retry;

    /**
     * @var int
     */
    private $expire;

    /**
     * @var int
     */
    private $minTTL;

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
        $template = '%s %s %s %s %s %s %s';
        return vsprintf($template, $this->toArray());
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
        return \serialize($this->toArray());
    }

    public function unserialize($serialized): void
    {
        $unserialized = \unserialize($serialized);
        $this->mname = new Hostname($unserialized['mname']);
        $this->rname = new Hostname($unserialized['rname']);
        $this->serial = $unserialized['serial'];
        $this->refresh = $unserialized['refresh'];
        $this->retry = $unserialized['retry'];
        $this->expire = $unserialized['expire'];
        $this->minTTL = $unserialized['minimumTTL'];
    }
}
