<?php
namespace RemotelyLiving\PHPDNS\Resolvers;

use RemotelyLiving\PHPDNS\Entities\DNSRecordCollection;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Entities\IPAddress;
use RemotelyLiving\PHPDNS\Mappers\LocalSystem as LocalMapper;
use RemotelyLiving\PHPDNS\Resolvers\Interfaces\ReverseDNSQuery;
use RemotelyLiving\PHPDNS\Services\Interfaces\LocalSystemDNS;
use RemotelyLiving\PHPDNS\Services\LocalSystemDNS as LocalDNSService;

class LocalSystem extends ResolverAbstract implements ReverseDNSQuery
{
    /**
     * @var \RemotelyLiving\PHPDNS\Services\Interfaces\LocalSystemDNS
     */
    private $systemDNS;

    /**
     * @var \RemotelyLiving\PHPDNS\Mappers\LocalSystem
     */
    private $mapper;

    public function __construct(LocalSystemDNS $systemDNS = null, LocalMapper $mapper = null)
    {
        $this->systemDNS = $systemDNS ?? new LocalDNSService();
        $this->mapper = $mapper ?? new LocalMapper();
    }

    public function getHostnameByAddress(string $IPAddress): Hostname
    {
        $result = $this->systemDNS->getHostnameByAddress(new IPAddress($IPAddress));

        return Hostname::createFromString($result);
    }

    protected function doQuery(Hostname $hostname, DNSRecordType $type): DNSRecordCollection
    {
        $results = $this->systemDNS->getRecord(
            $hostname->getHostnameWithoutTrailingDot(), // dns_get_record doesn't like trailing dot as much!
            $this->mapper->getTypeCodeFromType($type)
        );

        $collection = new DNSRecordCollection();

        foreach ($results as $result) {
            $collection[] = $this->mapper->mapFields($result)->toDNSRecord();
        }

        return $collection;
    }
}
