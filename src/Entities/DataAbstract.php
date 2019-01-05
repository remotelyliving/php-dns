<?php
namespace RemotelyLiving\PHPDNS\Entities;

use RemotelyLiving\PHPDNS\Entities\Interfaces\Arrayable;
use RemotelyLiving\PHPDNS\Entities\Interfaces\Serializable;
use RemotelyLiving\PHPDNS\Exceptions\InvalidArgumentException;

abstract class DataAbstract implements Arrayable, Serializable
{
    abstract public function __toString();

    public function equals(DataAbstract $dataAbstract): bool
    {
        return (string)$this === (string)$dataAbstract;
    }

    public static function createFromTypeAndString(DNSRecordType $recordType, string $data): self
    {
        if ($recordType->isA(DNSRecordType::TYPE_TXT)) {
            return new TXTData($data);
        }

        if ($recordType->isA(DNSRecordType::TYPE_NS)) {
            return new NSData(new Hostname($data));
        }

        if ($recordType->isA(DNSRecordType::TYPE_CNAME)) {
            return new CNAMEData(new Hostname($data));
        }

        $parsed = self::parseDataToArray($data);

        if ($recordType->isA(DNSRecordType::TYPE_MX)) {
            return new MXData(new Hostname($parsed[1]), (int)$parsed[0]);
        }

        if ($recordType->isA(DNSRecordType::TYPE_SOA)) {
            return new SOAData(
                new Hostname($parsed[0]),
                new Hostname($parsed[1]),
                (int)$parsed[2] ?? 0,
                (int)$parsed[3] ?? 0,
                (int)$parsed[4] ?? 0,
                (int)$parsed[5] ?? 0,
                (int)$parsed[6] ?? 0
            );
        }



        if ($recordType->isA(DNSRecordType::TYPE_CAA)) {
            return new CAAData($parsed[0], $parsed[1], $parsed[2]);
        }

        throw new InvalidArgumentException("{$data} could not be created with type {$recordType}");
    }

    private static function parseDataToArray(string $data): array
    {
        return explode(' ', $data);
    }
}
