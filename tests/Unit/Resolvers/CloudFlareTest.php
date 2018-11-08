<?php
namespace RemotelyLiving\PHPDNS\Tests\Unit\Resolvers;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Response;
use RemotelyLiving\PHPDNS\Entities\DNSRecord;
use RemotelyLiving\PHPDNS\Entities\DNSRecordCollection;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Entities\IPAddress;
use RemotelyLiving\PHPDNS\Mappers\GoogleDNS as GoogleMapper;
use RemotelyLiving\PHPDNS\Resolvers\CloudFlare;
use RemotelyLiving\PHPDNS\Resolvers\GoogleDNS;
use RemotelyLiving\PHPDNS\Resolvers\ResolverAbstract;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

// @codingStandardsIgnoreFile
class CloudFlareTest extends BaseTestAbstract
{
    /**
     * @var \GuzzleHttp\ClientInterface
     */
    private $httpClient;

    /**
     * @var \RemotelyLiving\PHPDNS\Resolvers\CloudFlare
     */
    private $cloudFlare;

    protected function setUp()
    {
        $this->httpClient = $this->createMock(ClientInterface::class);
        $this->cloudFlare = new CloudFlare($this->httpClient);
        $this->assertInstanceOf(ResolverAbstract::class, $this->cloudFlare);
    }

    /**
     * @test
     */
    public function hasOrDoesNotHaveRecord()
    {
        $hostname = Hostname::createFromString('facebook.com');
        $type = DNSRecordType::createFromString('A');
        $record = DNSRecord::createFromPrimitives('A', 'facebook.com', 1726, IPAddress::createFromString('2606:2800:220:1:248:1893:25c8:1946'));
        $nonMatchResponse = self::buildResponseBasedOnType(255);
        $goodResponse = self::buildResponseBasedOnType($type->toInt());

        $this->httpClient->expects($this->exactly(2))
            ->method('request')
            ->with('GET', "/dns-query?name={$hostname}&type={$type}")
            ->willReturnOnConsecutiveCalls(
                new Response('', [], $nonMatchResponse),
                new Response('', [], $goodResponse)
            );

        $this->assertFalse($this->cloudFlare->hasRecord($record));
        $this->assertTrue($this->cloudFlare->hasRecord($record));
    }

    /**
     * @test
     * @dataProvider dnsQueryInterfaceMessageProvider
     */
    public function getsRecords(string $method, Hostname $hostname, DNSRecordType $type, string $response, $expected)
    {
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', "/dns-query?name={$hostname}&type={$type}")
            ->willReturn(new Response('', [], $response));

        $actual = $this->cloudFlare->{$method}($hostname, $type);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     * @dataProvider httpExceptionProvider
     * @expectedException \RemotelyLiving\PHPDNS\Resolvers\Exceptions\QueryFailure
     */
    public function getsRecordsAndThrowsQueryExceptionOnFailures(\Throwable $e)
    {
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with('GET', "/dns-query?name=facebook.com&type=AAAA")
            ->willThrowException($e);

        $this->cloudFlare->getAAAARecords(Hostname::createFromString('facebook.com'));
    }

    public function httpExceptionProvider(): array
    {
        return [
            [$this->createMock(ConnectException::class)],
            [$this->createMock(ClientException::class)],
        ];
    }

    public function dnsQueryInterfaceMessageProvider(): array
    {
        $AAAARecord = DNSRecord::createFromPrimitives('AAAA', 'facebook.com', 1726, IPAddress::createFromString('2606:2800:220:1:248:1893:25c8:1946'));
        $CNAMERecord = DNSRecord::createFromPrimitives('CNAME', 'facebook.com', 1726, IPAddress::createFromString('2606:2800:220:1:248:1893:25c8:1946'));

        $AAAAColection = new DNSRecordCollection($AAAARecord);
        $CNAMECollection = new DNSRecordCollection($CNAMERecord);
        $emptyCollection = new DNSRecordCollection();
        $hostname = Hostname::createFromString('facebook.com');

        return [
            ['getARecords', $hostname, DNSRecordType::createA(), self::getEmptyResponse(), $emptyCollection],
            ['getAAAARecords',$hostname, DNSRecordType::createAAAA(), self::buildResponseBasedOnType(DNSRecordType::createAAAA()->toInt()), $AAAAColection],
            ['getCNAMERecords',$hostname, DNSRecordType::createCNAME(), self::buildResponseBasedOnType(DNSRecordType::createCNAME()->toInt()), $CNAMECollection],
            ['getTXTRecords', $hostname, DNSRecordType::createTXT(), self::getEmptyResponse(), $emptyCollection],
            ['getMXRecords', $hostname, DNSRecordType::createMX(), self::getAuthoritativeResponse(), $emptyCollection],
            ['recordTypeExists', $hostname, DNSRecordType::createMX(), self::buildResponseBasedOnType(DNSRecordType::createMX()->toInt()), true],
            ['recordTypeExists', $hostname, DNSRecordType::createMX(), self::getEmptyResponse(), false],
        ];
    }

    public static function buildResponseBasedOnType(int $type): string
    {
        $json = '{"Status":0,"TC":false,"RD":true,"RA":true,"AD":true,"CD":false,"Question":[{"name":"example.com","type":28}],"Answer":[{"name":"facebook.com.","type":28,"TTL":1726,"data":"2606:2800:220:1:248:1893:25c8:1946"}]}';

        $decoded = \json_decode($json, true);
        $decoded['Answer'][0]['type'] = $type;

        return json_encode($decoded);
    }

    public static function getEmptyResponse(): string
    {
        return '{"Status": 3,"TC": false,"RD": true,"RA": true,"AD": true,"CD": false,"Question":[]}';
    }

    public static function getAuthoritativeResponse(): string
    {
        return '{"Status": 3,"TC": false,"RD": true,"RA": true,"AD": false,"CD": false,"Question":[{"name": "a.club.","type": 16}],"Authority":[{"name": "a.club","type": 6,"TTL": 59,"data": "ns1.dns.nic.club. hostmaster.neustar.biz. 1541917336 900 900 604800 60"}],"Comment": "Response from 156.154.157.215."}';
    }
}
