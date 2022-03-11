<?php

namespace RemotelyLiving\PHPDNS\Entities\Interfaces;

use JsonSerializable;

interface Serializable extends JsonSerializable
{
    public function __serialize(): array;

    public function __unserialize(array $data): void;

    public function jsonSerialize(): array;
}
