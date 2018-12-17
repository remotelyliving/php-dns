<?php
namespace RemotelyLiving\PHPDNS\Tests\Unit\Resolvers;

use RemotelyLiving\PHPDNS\Entities\DNSRecord;
use RemotelyLiving\PHPDNS\Entities\DNSRecordCollection;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Entities\IPAddress;
use RemotelyLiving\PHPDNS\Resolvers\LocalSystem;
use RemotelyLiving\PHPDNS\Resolvers\ResolverAbstract;
use RemotelyLiving\PHPDNS\Services\Interfaces\LocalSystemDNS;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;
use RemotelyLiving\PHPDNS\Mappers\LocalSystem as LocalSystemMapper;

// @codingStandardsIgnoreFile
class LocalSystemTest extends BaseTestAbstract
{
    /**
     * @var \RemotelyLiving\PHPDNS\Services\Interfaces\LocalSystemDNS
     */
    private $dnsClient;

    /**
     * @var \RemotelyLiving\PHPDNS\Mappers\LocalSystem
     */
    private $localSystemMapper;

    /**
     * @var \RemotelyLiving\PHPDNS\Resolvers\LocalSystem
     */
    private $localSystem;

    protected function setUp()
    {
        $this->dnsClient = $this->createMock(LocalSystemDNS::class);
        $this->localSystemMapper = new LocalSystemMapper();
        $this->localSystem = new LocalSystem($this->dnsClient);
        $this->assertInstanceOf(ResolverAbstract::class, $this->localSystem);
    }

    /**
     * @test
     */
    public function hasOrDoesNotHaveRecord()
    {
        $record = DNSRecord::createFromPrimitives('A', 'facebook.com', 1726, IPAddress::createFromString('192.169.1.1'));

        $this->dnsClient->expects($this->exactly(2))
            ->method('getRecord')
            ->with('facebook.com', 1)
            ->willReturnOnConsecutiveCalls(
                self::getEmptyResponse(),
                self::buildResponse('A')
            );

        $this->assertFalse($this->localSystem->hasRecord($record));
        $this->assertTrue($this->localSystem->hasRecord($record));
    }

    /**
     * @test
     */
    public function getsHostnameByAddress()
    {
        $expected = Hostname::createFromString('cnn.com');
        $IPAddress = IPAddress::createFromString('127.0.0.1');

        $this->dnsClient->method('getHostnameByAddress')
            ->with('127.0.0.1')
            ->willReturn('cnn.com');

        $this->assertEquals($expected, $this->localSystem->getHostnameByAddress($IPAddress));
    }

    /**
     * @test
     * @dataProvider dnsQueryInterfaceMessageProvider
     */
    public function getsRecords(string $method, Hostname $hostname, DNSRecordType $type, array $response, $expected)
    {
        $this->dnsClient->expects($this->once())
            ->method('getRecord')
            ->with($hostname->getHostnameWithoutTrailingDot(), $this->localSystemMapper->getTypeCodeFromType($type))
            ->willReturn($response);

        $actual = $this->localSystem->{$method}($hostname, $type);

        $this->assertEquals($expected, $actual);
    }

    public function dnsQueryInterfaceMessageProvider(): array
    {
        $ARecord = DNSRecord::createFromPrimitives(
            'A',
            'facebook.com',
            375,
            IPAddress::createFromString('192.169.1.1')
        );

        $CNAMERecord = DNSRecord::createFromPrimitives(
            'CNAME',
            'facebook.com',
            375,
            IPAddress::createFromString('192.169.1.1')
        );

        $MXRecord = DNSRecord::createFromPrimitives(
            'MX',
            'facebook.com',
            375,
            IPAddress::createFromString('192.169.1.1')
        );

        $hostname = Hostname::createFromString('facebook.com');

        return [
            ['getARecords', $hostname, DNSRecordType::createA(), self::buildResponse('A'), new DNSRecordCollection($ARecord)],
            ['getAAAARecords',$hostname, DNSRecordType::createAAAA(), self::getEmptyResponse(), new DNSRecordCollection()],
            ['getCNAMERecords',$hostname, DNSRecordType::createCNAME(), self::buildResponse('CNAME'), new DNSRecordCollection($CNAMERecord)],
            ['getTXTRecords', $hostname, DNSRecordType::createTXT(), self::getEmptyResponse(), new DNSRecordCollection()],
            ['getMXRecords', $hostname, DNSRecordType::createMX(), self::buildResponse('MX'), new DNSRecordCollection($MXRecord)],
            ['recordTypeExists', $hostname, DNSRecordType::createMX(), self::buildResponse('MX'), true],
            ['recordTypeExists', $hostname, DNSRecordType::createMX(), self::getEmptyResponse(), false],
        ];
    }

    public static function buildResponse(string $type): array
    {
        return [
            [
                'host' => 'facebook.com',
                'class' => 'IN',
                'ttl' => 375,
                'type' => $type,
                'ipv6' => '192.169.1.1',
            ],
        ];
    }

    public static function getEmptyResponse(): array
    {
        return [];
    }
}
