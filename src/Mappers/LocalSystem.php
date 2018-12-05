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
        $data = null;

        if (isset($this->fields['ipv6'])) {
            $IPAddress = $this->fields['ipv6'];
        }

        if (isset($this->fields['ip'])) {
            $IPAddress = $this->fields['ip'];
        }

        if (isset($this->fields['txt'])) {
            $data = $this->fields['txt'];
        }

        if (isset($this->fields['target'])) {
            $data = $this->fields['target'];
        }

        if (isset($this->fields['target']) && isset($this->fields['pri'])) {
            $data = "{$this->fields['pri']} {$this->fields['target']}";
        }

        if (isset($this->fields['mname'])) {
            $template = '%s %s %s %s %s %s %s';
            $data = sprintf(
                $template,
                $this->fields['mname'],
                $this->fields['rname'],
                $this->fields['serial'],
                $this->fields['refresh'],
                $this->fields['retry'],
                $this->fields['expire'],
                $this->fields['minimum-ttl']
            );
        }

        return DNSRecord::createFromPrimitives(
            $this->fields['type'],
            $this->fields['host'],
            $this->fields['ttl'],
            $IPAddress,
            $this->fields['class'],
            $data
        );
    }

    public function getTypeCodeFromType(DNSRecordType $type): int
    {
        return array_flip(self::PHP_CODE_TYPE_MAP)[(string)$type] ?? \DNS_ANY;
    }
}
