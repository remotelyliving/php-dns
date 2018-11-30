<?php
namespace RemotelyLiving\PHPDNS\Mappers;

use RemotelyLiving\PHPDNS\Entities\DNSRecord;

interface MapperInterface
{
    public function mapFields(array $record): MapperAbstract;

    public function toDNSRecord(): DNSRecord;
}
