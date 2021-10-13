<?php

namespace RemotelyLiving\PHPDNS\Mappers;

use RemotelyLiving\PHPDNS\Entities\DNSRecord;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Interfaces\DNSRecordInterface;

final class Dig extends MapperAbstract
{
    public function toDNSRecord(): DNSRecordInterface
    {
        $type = new DNSRecordType($this->fields[3]);
        if ($type->isA(DNSRecordType::TYPE_A) || $type->isA(DNSRecordType::TYPE_AAAA)) {
            return DNSRecord::createFromPrimitives(
                $this->fields[3],
                (string)$this->fields[0],
                (int)$this->fields[1],
                $this->fields[4]
            );
        }

        return DNSRecord::createFromPrimitives(
            $this->fields[3],
            (string)$this->fields[0],
            (int)$this->fields[1],
            null,
            'IN',
            $this->fields[4]
        );
    }
}
