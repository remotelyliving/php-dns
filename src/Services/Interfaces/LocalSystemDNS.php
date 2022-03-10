<?php

namespace RemotelyLiving\PHPDNS\Services\Interfaces;

interface LocalSystemDNS
{
    /**
     *
     * @throws \RemotelyLiving\PHPDNS\Resolvers\Exceptions\ReverseLookupFailure
     */
    public function getHostnameByAddress(string $IPAddress): string;

    /**
     *
     * @throws \RemotelyLiving\PHPDNS\Resolvers\Exceptions\QueryFailure
     */
    public function getRecord(string $hostname, int $type): array;
}
