<?php

namespace RemotelyLiving\PHPDNS\Mappers;

use RemotelyLiving\PHPDNS\Entities\Interfaces\DNSRecordInterface;

interface MapperInterface
{
    public function mapFields(array $fields): MapperInterface;

    public function toDNSRecord(): DNSRecordInterface;
}
