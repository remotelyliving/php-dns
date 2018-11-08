<?php
namespace RemotelyLiving\PHPDNS\Mappers;

use RemotelyLiving\PHPDNS\Entities\DNSRecord;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;

class LocalSystem extends MapperAbstract
{
    private const PHP_CODE_TYPE_MAP = [
        \DNS_A => DNSRecordType::TYPE_A,
        \DNS_CNAME => DNSRecordType::TYPE_CNAME,
        \DNS_HINFO => DNSRecordType::TYPE_HINFO,
        \DNS_CAA => DNSRecordType::TYPE_CAA,
        \DNS_MX => DNSRecordType::TYPE_MX,
        \DNS_NS => DNSRecordType::TYPE_NS,
        \DNS_PTR => DNSRecordType::TYPE_PTR,
        \DNS_SOA => DNSRecordType::TYPE_SOA,
        \DNS_TXT => DNSRecordType::TYPE_TXT,
        \DNS_AAAA => DNSRecordType::TYPE_AAAA,
        \DNS_SRV => DNSRecordType::TYPE_SRV,
        \DNS_NAPTR => DNSRecordType::TYPE_NAPTR,
        \DNS_A6 => DNSRecordType::TYPE_A6,
        \DNS_ANY => DNSRecordType::TYPE_ANY,
    ];

    public function toDNSRecord(): DNSRecord
    {
        $IPAddress = null;

        if (isset($this->record['ipv6'])) {
            $IPAddress = $this->record['ipv6'];
        }

        if (isset($this->record['ip'])) {
            $IPAddress = $this->record['ip'];
        }

        return DNSRecord::createFromPrimitives(
            $this->record['type'],
            $this->record['host'],
            $this->record['ttl'],
            $IPAddress
        );
    }

    public function getTypeCodeFromType(DNSRecordType $type): int
    {
        return array_flip(self::PHP_CODE_TYPE_MAP)[(string)$type] ?? \DNS_ANY;
    }
}
