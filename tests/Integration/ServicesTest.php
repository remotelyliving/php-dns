<?php

namespace RemotelyLiving\PHPDNS\Tests\Integration;

use RemotelyLiving\PHPDNS\Resolvers\Exceptions\ReverseLookupFailure;

use const DNS_ALL;

class ServicesTest extends BaseTestAbstract
{
    /**
     * @test
     */
    public function localDNSServiceProvidesRecords(): void
    {
        $localDNS = $this->createLocalSystemDNS();

        $records = $localDNS->getRecord('google.com', DNS_A);
        $this->assertNotEmpty($records);
    }

    /**
     * @test
     */
    public function localDNSServiceProvidesReverseLookup(): void
    {
        $localDNS = $this->createLocalSystemDNS();

        $hostname = $localDNS->getHostnameByAddress('127.0.0.1');
        $this->assertSame('localhost', $hostname);
    }

    /**
     * @test
     */
    public function throwsOnReverseLookupFailure(): void
    {
        $this->expectException(ReverseLookupFailure::class);
        $this->createLocalSystemDNS()->getHostnameByAddress('0.0.0.0');
    }
}
