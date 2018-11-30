<?php
namespace RemotelyLiving\PHPDNS\Mappers;

use RemotelyLiving\PHPDNS\Entities\DNSRecord;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;

abstract class MapperAbstract implements MapperInterface
{
    /**
     * @var array
     */
    protected $fields = [];

    public function __construct(array $fields = [])
    {
        $this->fields = $fields;
    }

    public function mapFields(array $fields): self
    {
        return new static($fields);
    }

    abstract public function toDNSRecord(): DNSRecord;
}
