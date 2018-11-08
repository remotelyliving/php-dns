<?php
namespace RemotelyLiving\PHPDNS\Entities\Interfaces;

interface Serializable extends \Serializable
{
    public function serialize(): string;
    public function unserialize($serialized): void;
}
