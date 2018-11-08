<?php
namespace RemotelyLiving\PHPDNS\Resolvers\Interfaces;

use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Entities\IPAddress;

interface ReverseDNSQuery
{
    /**
     * @param \RemotelyLiving\PHPDNS\Entities\IPAddress $IPAddress
     *
     * @return \RemotelyLiving\PHPDNS\Entities\Hostname
     *
     * @throws \RemotelyLiving\PHPDNS\Resolvers\Exceptions\ReverseLookupFailure
     */
    public function getHostnameByAddress(IPAddress $IPAddress): Hostname;
}
