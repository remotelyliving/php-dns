<?php
namespace RemotelyLiving\PHPDNS\Entities;

class SRVData extends DataAbstract
{
    /**
     * @var string
     */
    private $value;
    private $weight;
    private $target;
    private $priority;
    private $port;

    public function __construct(int $priority, int $weight, int $port, Hostname $target, string $value)
    {
        $this->priority = $priority;
        $this->target = $target;
        $this->value = $value;
        $this->weight = $weight;
        $this->port = $port;
        
    }

    public function __toString(): string
    {
        return "{$this->priority} {$this->weight} {$this->port} {$this->target}";
    }

    public function getPriority(): string
    {
        return $this->priority;
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getWeight(): string
    {
        return $this->weight;
    }

    public function toArray(): array
    {
        return [
            'priority' => $this->priority,
            'weight'  => $this->weight,
            'port'    => $this->port,
            'target' => $this->target,
            'value' => $this->value                        
        ];
    }

    public function serialize(): string
    {
        return \serialize($this->toArray());
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized): void
    {
        $unserialized = \unserialize($serialized);
        $this->priority = $unserialized['priority'];
        $this->weight = $unserialized['weight'];
        $this->port = $unserialized['port'];
        $this->target = new Hostname($unserialized['target']);
        $this->value = $unserialized['value'];
        
    }
}
