<?php
namespace RemotelyLiving\PHPDNS\Mappers;

use RemotelyLiving\PHPDNS\Entities\DNSRecord;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Entities\IPAddress;

class GoogleDNS extends MapperAbstract
{
    public function toDNSRecord(): DNSRecord
    {
        $type = DNSRecordType::createFromInt((int) $this->fields['type']);
        $IPAddress = (isset($this->fields['data']) && IPAddress::isValid($this->fields['data']))
            ? $this->fields['data']
            : null;

        $value = (isset($this->fields['data']) && !$IPAddress)
            ? str_ireplace('"', '', (string)$this->fields['data'])
            : null;

        return DNSRecord::createFromPrimitives(
            $type,
            $this->fields['name'],
            $this->fields['TTL'],
            $IPAddress,
            'IN',
            $value
        );
    }
}
