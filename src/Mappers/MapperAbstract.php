<?php
namespace RemotelyLiving\PHPDNS\Mappers;

use RemotelyLiving\PHPDNS\Entities\DNSRecord;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;

abstract class MapperAbstract implements MapperInterface
{
    /**
     * @var array
     */
    protected $record = [];

    public function __construct(array $record = [])
    {
        $this->record = $record;
    }

    public function mapRecord(array $record): self
    {
        return new static($record);
    }

    abstract public function toDNSRecord(): DNSRecord;
}
