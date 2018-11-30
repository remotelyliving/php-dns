<?php
namespace RemotelyLiving\PHPDNS\Mappers;

use RemotelyLiving\PHPDNS\Entities\DNSRecord;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\IPAddress;

class GoogleDNS extends MapperAbstract
{
    public function toDNSRecord(): DNSRecord
    {
        $IPAddress = (isset($this->fields['data']) && IPAddress::isValid($this->fields['data']))
            ? $this->fields['data']
            : null;

        return DNSRecord::createFromPrimitives(
            DNSRecordType::createFromInt((int) $this->fields['type']),
            substr($this->fields['name'], 0, -1),
            $this->fields['TTL'],
            $IPAddress
        );
    }
}
