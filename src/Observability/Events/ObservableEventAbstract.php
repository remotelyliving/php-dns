<?php

namespace RemotelyLiving\PHPDNS\Observability\Events;

use JsonSerializable;
use RemotelyLiving\PHPDNS\Entities\Interfaces\Arrayable;
use Symfony\Component\EventDispatcher\GenericEvent;

abstract class ObservableEventAbstract extends GenericEvent implements
    JsonSerializable,
    Arrayable
{
    abstract public static function getName(): string;

    public function jsonSerialize(): array
    {
        return [
            $this::getName() => $this->toArray(),
        ];
    }
}
