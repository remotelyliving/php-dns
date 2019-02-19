<?php
namespace RemotelyLiving\PHPDNS\Mappers;

use RemotelyLiving\PHPDNS\Entities\DNSRecord;

interface MapperInterface
{
    public function mapFields(array $record): MapperInterface;

    public function toDNSRecord(): DNSRecord;
}
