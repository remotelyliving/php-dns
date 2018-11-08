<?php
namespace RemotelyLiving\PHPDNS\Tests\Integration;

class ServicesTest extends BaseTestAbstract
{
    /**
     * @test
     */
    public function localDNSServiceProvidesRecords()
    {
        $localDNS = $this->createLocalSystemDNS();

        $records = $localDNS->getRecord('google.com', \DNS_ALL);
        $this->assertNotEmpty($records);

        $resultOfBadOperation = $localDNS->getRecord('l', \DNS_ALL);
        $this->assertEquals([], $resultOfBadOperation);
    }

    /**
     * @test
     */
    public function localDNSServiceProvidesReverseLookup()
    {
        $localDNS = $this->createLocalSystemDNS();

        $hostname = $localDNS->getHostnameByAddress('127.0.0.1');
        $this->assertSame('localhost', $hostname);
    }

    /**
     * @test
     * @expectedException \RemotelyLiving\PHPDNS\Resolvers\Exceptions\ReverseLookupFailure
     */
    public function throwsOnReverseLookupFailure()
    {
        $this->createLocalSystemDNS()->getHostnameByAddress('40.1.1.40');
    }
}
