<?php

namespace RemotelyLiving\PHPDNS\Mappers;

use RemotelyLiving\PHPDNS\Entities\CAAData;
use RemotelyLiving\PHPDNS\Entities\CNAMEData;
use RemotelyLiving\PHPDNS\Entities\DNSRecord;
use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Entities\Interfaces\DNSRecordInterface;
use RemotelyLiving\PHPDNS\Entities\MXData;
use RemotelyLiving\PHPDNS\Entities\NSData;
use RemotelyLiving\PHPDNS\Entities\SOAData;
use RemotelyLiving\PHPDNS\Entities\SRVData;
use RemotelyLiving\PHPDNS\Entities\TXTData;
use RemotelyLiving\PHPDNS\Exceptions\InvalidArgumentException;

final class Dig extends MapperAbstract
{
    public function toDNSRecord(): DNSRecordInterface
    {
        $baseRecord = DNSRecord::createFromPrimitives(
            $this->fields['type'],
            $this->fields['host'],
            $this->fields['ttl'],
            $this->fields['ip'] ?? $this->fields['ipv6'] ?? null,
            $this->fields['class'],
        );

        return match ((string) $this->fields['type']) {
            'A', 'AAAA' => $baseRecord,
            'CAA' => $baseRecord
               ->setData(new CAAData($this->fields['flags'], $this->fields['tag'], $this->fields['value'])),
            'CNAME' => $baseRecord
               ->setData(new CNAMEData(Hostname::createFromString($this->fields['target']))),
            'MX' => $baseRecord
               ->setData(new MXData(Hostname::createFromString($this->fields['target']), $this->fields['pri'])),
            'NS' => $baseRecord
               ->setData(new NSData(Hostname::createFromString($this->fields['target']))),
            'SOA' => $baseRecord
               ->setData(new SOAData(
                   Hostname::createFromString($this->fields['mname']),
                   Hostname::createFromString($this->fields['rname']),
                   $this->fields['serial'],
                   $this->fields['refresh'],
                   $this->fields['retry'],
                   $this->fields['expire'],
                   $this->fields['minimum_ttl'],
               )),
            'SRV' => $baseRecord
               ->setData(new SRVData(
                   $this->fields['pri'],
                   $this->fields['weight'],
                   $this->fields['port'],
                   Hostname::createFromString($this->fields['target'])
               )),
            'TXT' => $baseRecord
               ->setData(new TXTData($this->fields['txt'])),
            default => throw new InvalidArgumentException($this->fields['type'] . ' is not supported by dig')
        };
    }
}
