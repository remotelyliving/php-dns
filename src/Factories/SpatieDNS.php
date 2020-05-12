<?php

namespace RemotelyLiving\PHPDNS\Factories;

use RemotelyLiving\PHPDNS\Entities\Hostname;
use Spatie\Dns\Dns;

class SpatieDNS
{
    public function createResolver(Hostname $domain, Hostname $nameserver = null): Dns
    {
        return new Dns((string) $domain, (string) $nameserver);
    }
}
