<?php
namespace RemotelyLiving\PHPDNS\Resolvers\Interfaces;

use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Entities\IPAddress;

interface ReverseDNSQuery
{
    /**
     * @throws \RemotelyLiving\PHPDNS\Resolvers\Exceptions\ReverseLookupFailure
     */
    public function getHostnameByAddress(string $IPAddress): Hostname;
}
