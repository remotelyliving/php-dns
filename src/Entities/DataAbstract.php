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

        if ($recordType->isA(DNSRecordType::TYPE_MX)) {
            $exploded = explode(' ', $data);

            return new MXData($exploded[1], (int)$exploded[0]);
        }

        if ($recordType->isA(DNSRecordType::TYPE_SOA)) {
            $exploded = explode(' ', $data);

            return new SOAData(
                new Hostname($exploded[0]),
                new Hostname($exploded[1]),
                (int)$exploded[2] ?? 0,
                (int)$exploded[3] ?? 0,
                (int)$exploded[4] ?? 0,
                (int)$exploded[5] ?? 0,
                (int)$exploded[6] ?? 0
            );
        }

        if ($recordType->isA(DNSRecordType::TYPE_NS)) {
            return new NSData($data);
        }

        throw new InvalidArgumentException("{$data} could not be created with type {$recordType}");
    }
}
