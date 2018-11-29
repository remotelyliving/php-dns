<?php
namespace RemotelyLiving\PHPDNS\Services;

use RemotelyLiving\PHPDNS\Resolvers\Exceptions\QueryFailure;
use RemotelyLiving\PHPDNS\Resolvers\Exceptions\ReverseLookupFailure;
use \RemotelyLiving\PHPDNS\Services\Interfaces\LocalSystemDNS as LocalSystemDNSInterface;

final class LocalSystemDNS implements LocalSystemDNSInterface
{
    public function getRecord(string $hostname, int $type): array
    {
        $results = @dns_get_record($hostname, $type);

        // this is untestable without creating a system networking failure
        // @codeCoverageIgnoreStart
        if ($results === false) {
            throw new QueryFailure();
        }
        // @codeCoverageIgnoreEnd

        return $results;
    }

    public function getHostnameByAddress(string $IPAddress): string
    {
        $hostname = @gethostbyaddr($IPAddress);

        if ($hostname === $IPAddress || $hostname === false) {
            throw new ReverseLookupFailure();
        }

        return $hostname;
    }
}
