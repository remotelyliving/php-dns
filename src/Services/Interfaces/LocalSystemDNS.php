<?php
namespace RemotelyLiving\PHPDNS\Services\Interfaces;

interface LocalSystemDNS
{
    /**
     * @param string $IPAddress
     * @return string
     *
     * @throws \RemotelyLiving\PHPDNS\Resolvers\Exceptions\ReverseLookupFailure
     */
    public function getHostnameByAddress(string $IPAddress): string;

    /**
     * @param string $hostname
     * @param int $type
     *
     * @return array
     *
     * @throws \RemotelyLiving\PHPDNS\Resolvers\Exceptions\QueryFailure
     */
    public function getRecord(string $hostname, int $type): array;
}
