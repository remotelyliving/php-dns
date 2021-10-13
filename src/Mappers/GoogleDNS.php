<?php

namespace RemotelyLiving\PHPDNS\Mappers;

use RemotelyLiving\PHPDNS\Entities\DNSRecord;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Entities\Interfaces\DNSRecordInterface;
use RemotelyLiving\PHPDNS\Entities\IPAddress;

use function str_ireplace;

final class GoogleDNS extends MapperAbstract
{
    /**
     * @var string
     */
    private const DATA = 'data';
    public function toDNSRecord(): DNSRecordInterface
    {
        $type = DNSRecordType::createFromInt((int) $this->fields['type']);
        $IPAddress = (isset($this->fields[self::DATA]) && IPAddress::isValid($this->fields[self::DATA]))
            ? $this->fields[self::DATA]
            : null;

        $value = (isset($this->fields[self::DATA]) && !$IPAddress)
            ? str_ireplace('"', '', (string)$this->fields[self::DATA])
            : null;

        return DNSRecord::createFromPrimitives(
            (string)$type,
            $this->fields['name'],
            $this->fields['TTL'],
            $IPAddress,
            'IN',
            $value
        );
    }
}
