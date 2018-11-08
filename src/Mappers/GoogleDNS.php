<?php
namespace RemotelyLiving\PHPDNS\Mappers;

use RemotelyLiving\PHPDNS\Entities\DNSRecord;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\IPAddress;

class GoogleDNS extends MapperAbstract
{
    public function toDNSRecord(): DNSRecord
    {
        $IPAddress = (isset($this->record['data']) && IPAddress::isValid($this->record['data']))
            ? $this->record['data']
            : null;

        return DNSRecord::createFromPrimitives(
            DNSRecordType::createFromInt((int) $this->record['type']),
            substr($this->record['name'], 0, -1),
            $this->record['TTL'],
            $IPAddress
        );
    }
}
