<?php

namespace RemotelyLiving\PHPDNS\Mappers;

use RemotelyLiving\PHPDNS\Entities\Interfaces\DNSRecordInterface;

abstract class MapperAbstract implements MapperInterface
{
    final public function __construct(protected array $fields = [])
    {
    }

    public function mapFields(array $fields): MapperInterface
    {
        return new static($fields);
    }

    abstract public function toDNSRecord(): DNSRecordInterface;
}
