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

        if (isset($this->fields['ipv6'])) {
            $IPAddress = $this->fields['ipv6'];
        }

        if (isset($this->fields['ip'])) {
            $IPAddress = $this->fields['ip'];
        }

        return DNSRecord::createFromPrimitives(
            $this->fields['type'],
            $this->fields['host'],
            $this->fields['ttl'],
            $IPAddress,
            $this->fields['class'],
            $this->formatData($this->fields)
        );
    }

    public function getTypeCodeFromType(DNSRecordType $type): int
    {
        return array_flip(self::PHP_CODE_TYPE_MAP)[(string)$type] ?? \DNS_ANY;
    }

    private function formatData(array $fields): ?string
    {
        if (isset($this->fields['flags'], $fields['tag'], $fields['value'])) {
            return "{$fields['flags']} {$fields['tag']} \"{$fields['value']}\"";
        }

        if (isset($fields['mname'])) {
            $template = '%s %s %s %s %s %s %s';
            return sprintf(
                $template,
                $fields['mname'],
                $fields['rname'],
                $fields['serial'],
                $fields['refresh'],
                $fields['retry'],
                $fields['expire'],
                $fields['minimum-ttl']
            );
        }

        if (isset($fields['target'], $fields['pri'])) {
            return "{$fields['pri']} {$fields['target']}";
        }

        if (isset($fields['target'])) {
            return $fields['target'];
        }

        if (isset($fields['txt'])) {
            return $fields['txt'];
        }

        return null;
    }
}
