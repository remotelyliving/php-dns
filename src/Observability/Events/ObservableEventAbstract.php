<?php
namespace RemotelyLiving\PHPDNS\Observability\Events;

use RemotelyLiving\PHPDNS\Entities\Interfaces\Arrayable;
use Symfony\Component\EventDispatcher\Event;

abstract class ObservableEventAbstract extends Event implements \JsonSerializable, Arrayable
{
    abstract public static function getName(): string;

    public function jsonSerialize(): array
    {
        return [
            $this::getName() => $this->toArray(),
        ];
    }
}
