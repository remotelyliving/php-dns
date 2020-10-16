<?php

namespace RemotelyLiving\PHPDNS\Mappers;

use RemotelyLiving\PHPDNS\Entities\Interfaces\DNSRecordInterface;

abstract class MapperAbstract implements MapperInterface
{
    protected array $fields = [];

    final public function __construct(array $fields = [])
    {
        $this->fields = $fields;
    }

    public function mapFields(array $fields): MapperInterface
    {
        return new static($fields);
    }

    abstract public function toDNSRecord(): DNSRecordInterface;
}
