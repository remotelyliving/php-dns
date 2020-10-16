<?php

namespace RemotelyLiving\PHPDNS\Entities\Interfaces;

use JsonSerializable;

interface Serializable extends \Serializable, JsonSerializable
{
    public function serialize(): string;

    public function unserialize($serialized): void;

    public function jsonSerialize(): array;
}
