<?php
namespace RemotelyLiving\PHPDNS\Entities;

class CNAMEData extends DataAbstract
{
    /**
     * @var \RemotelyLiving\PHPDNS\Entities\Hostname
     */
    private $hostname;

    public function __construct(Hostname $hostname)
    {
        $this->hostname = $hostname;
    }

    public function __toString(): string
    {
        return (string) $this->hostname;
    }

    public function getHostname(): Hostname
    {
        return $this->hostname;
    }

    public function toArray(): array
    {
        return [
            'hostname' => (string)$this->hostname,
        ];
    }

    public function serialize(): string
    {
        return \serialize($this->toArray());
    }

    public function unserialize($serialized): void
    {
        $unserialized = \unserialize($serialized);
        $this->hostname = new Hostname($unserialized['hostname']);
    }
}
