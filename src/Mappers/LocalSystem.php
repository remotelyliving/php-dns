<?php

namespace RemotelyLiving\PHPDNS\Mappers;

use RemotelyLiving\PHPDNS\Entities\DNSRecord;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Interfaces\DNSRecordInterface;

use function array_flip;
use function sprintf;

if (!defined('DNS_CAA')) {
    define('DNS_CAA', 8192);
}

use const DNS_A;
use const DNS_A6;
use const DNS_AAAA;
use const DNS_ANY;
use const DNS_CAA;
use const DNS_CNAME;
use const DNS_HINFO;
use const DNS_MX;
use const DNS_NAPTR;
use const DNS_NS;
use const DNS_PTR;
use const DNS_SOA;
use const DNS_SRV;
use const DNS_TXT;

final class LocalSystem extends MapperAbstract
{
    private const PHP_CODE_TYPE_MAP = [
        DNS_A => DNSRecordType::TYPE_A,
        DNS_CNAME => DNSRecordType::TYPE_CNAME,
        DNS_HINFO => DNSRecordType::TYPE_HINFO,
        DNS_CAA => DNSRecordType::TYPE_CAA,
        DNS_MX => DNSRecordType::TYPE_MX,
        DNS_NS => DNSRecordType::TYPE_NS,
        DNS_PTR => DNSRecordType::TYPE_PTR,
        DNS_SOA => DNSRecordType::TYPE_SOA,
        DNS_TXT => DNSRecordType::TYPE_TXT,
        DNS_AAAA => DNSRecordType::TYPE_AAAA,
        DNS_SRV => DNSRecordType::TYPE_SRV,
        DNS_NAPTR => DNSRecordType::TYPE_NAPTR,
        DNS_A6 => DNSRecordType::TYPE_A6,
        DNS_ANY => DNSRecordType::TYPE_ANY,
    ];
    /**
     * @var string
     */
    private const TARGET = 'target';
    /**
     * @var string
     */
    private const PRI = 'pri';
    /**
     * @var string
     */
    private const TEMPLATE = '%s %s %s %s %s %s %s';

    public function toDNSRecord(): DNSRecordInterface
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
        return array_flip(self::PHP_CODE_TYPE_MAP)[(string)$type] ?? DNS_ANY;
    }

    private function formatData(array $fields): ?string
    {
        if (isset($this->fields['flags'], $fields['tag'], $fields['value'])) {
            return "{$fields['flags']} {$fields['tag']} \"{$fields['value']}\"";
        }

        if (isset($fields['mname'])) {
            return sprintf(
                self::TEMPLATE,
                $fields['mname'],
                $fields['rname'],
                $fields['serial'],
                $fields['refresh'],
                $fields['retry'],
                $fields['expire'],
                $fields['minimum-ttl']
            );
        }

        if (isset($fields[self::TARGET], $fields[self::PRI], $fields['weight'], $fields['port'])) {
            return "{$fields[self::PRI]} {$fields['weight']} {$fields['port']} {$fields[self::TARGET]}";
        }


        if (isset($fields[self::TARGET], $fields[self::PRI])) {
            return "{$fields[self::PRI]} {$fields[self::TARGET]}";
        }

        if (isset($fields[self::TARGET])) {
            return $fields[self::TARGET];
        }

        if (isset($fields['txt'])) {
            return $fields['txt'];
        }

        return null;
    }
}
