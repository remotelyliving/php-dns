<?php
namespace RemotelyLiving\PHPDNS\Tests\Unit\Resolvers;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Promise\RejectionException;
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
            ->method('requestAsync')
            ->with('GET', "/dns-query?name={$hostname}&type={$type}")
            ->willReturnOnConsecutiveCalls(
                new FulfilledPromise(new Response(200, [], $nonMatchResponse)),
                new FulfilledPromise(new Response(200, [], $goodResponse))
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
            ->method('requestAsync')
            ->with('GET', "/dns-query?name={$hostname}&type={$type}")
            ->willReturn(new FulfilledPromise(new Response(200, [], $response)));

        $actual = $this->cloudFlare->{$method}($hostname, $type);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     */
    public function getsANYRecords()
    {
        $expected = new DNSRecordCollection(...[
            DNSRecord::createFromPrimitives('A', 'google.com', 1300, '192.168.1.1', 'IN'),
            DNSRecord::createFromPrimitives('MX', 'google.com', 1200, null, 'IN', '1 aspmx.l.google.com.')
        ]);

        $ARecordResponse = '{"Status": 0,"TC": false,"RD": true, "RA": true, "AD": false,"CD": false,"Question":[{"name": "google.com.", "type": 1}],"Answer":[{"name": "google.com.", "type": 1, "TTL": 1300, "data": "192.168.1.1"}]}';
        $MXRecordResponse = '{"Status": 0,"TC": false,"RD": true, "RA": true, "AD": false,"CD": false,"Question":[{"name": "google.com.", "type": 15}],"Answer":[{"name": "google.com.", "type": 15, "TTL": 1200, "data": "1 aspmx.l.google.com."}]}';
        $emptyResponse = $this->getEmptyResponse();

        $this->httpClient
            ->method('requestAsync')
            ->willReturnMap([
                ['GET', '/dns-query?name=google.com.&type=A', [], new FulfilledPromise(new Response(200, [], $ARecordResponse))],
                ['GET', '/dns-query?name=google.com.&type=MX', [], new FulfilledPromise(new Response(200, [], $MXRecordResponse))],
                ['GET', '/dns-query?name=google.com.&type=CNAME', [], new FulfilledPromise(new Response(200, [], $emptyResponse))],
                ['GET', '/dns-query?name=google.com.&type=HINFO', [], new FulfilledPromise(new Response(200, [], $emptyResponse))],
                ['GET', '/dns-query?name=google.com.&type=CAA', [], new FulfilledPromise(new Response(200, [], $emptyResponse))],
                ['GET', '/dns-query?name=google.com.&type=NS', [], new FulfilledPromise(new Response(200, [], $emptyResponse))],
                ['GET', '/dns-query?name=google.com.&type=PTR', [], new FulfilledPromise(new Response(200, [], $emptyResponse))],
                ['GET', '/dns-query?name=google.com.&type=SOA', [], new FulfilledPromise(new Response(200, [], $emptyResponse))],
                ['GET', '/dns-query?name=google.com.&type=TXT', [], new FulfilledPromise(new Response(200, [], $emptyResponse))],
                ['GET', '/dns-query?name=google.com.&type=AAAA', [], new FulfilledPromise(new Response(200, [], $emptyResponse))],
                ['GET', '/dns-query?name=google.com.&type=SRV', [], new FulfilledPromise(new Response(200, [], $emptyResponse))],
                ['GET', '/dns-query?name=google.com.&type=NAPTR', [], new FulfilledPromise(new Response(200, [], $emptyResponse))],
                ['GET', '/dns-query?name=google.com.&type=A6', [], new FulfilledPromise(new Response(200, [], $emptyResponse))],
            ]);

        $actual = $this->cloudFlare->getRecords('google.com');

        $this->assertEquals($expected, $actual);
    }


    /**
     * @test
     * @dataProvider httpExceptionProvider
     * @expectedException \RemotelyLiving\PHPDNS\Resolvers\Exceptions\QueryFailure
     */
    public function rethrowsAllHTTPExceptionsAsQueryFailures(RequestException $e)
    {
        $promise = new Promise(function () use (&$promise, $e) {
            $promise->reject($e);
        });

        $this->httpClient->expects($this->once())
            ->method('requestAsync')
            ->with('GET', '/dns-query?name=foo.&type=A')
            ->willReturn($promise);

        $this->cloudFlare->getARecords('foo');
    }

    /**
     * @test
     * @dataProvider httpExceptionProvider
     * @expectedException \RemotelyLiving\PHPDNS\Resolvers\Exceptions\QueryFailure
     */
    public function rethrowsAllHTTPExceptionsAsQueryFailuresForANYQuery(RequestException $e)
    {
        $promise = new Promise(function () use (&$promise, $e) {
            $promise->reject($e);
        });

        $this->httpClient->expects($this->any())
            ->method('requestAsync')
            ->willReturn($promise);

        $this->cloudFlare->getRecords('foo');
    }

    public function httpExceptionProvider(): array
    {
        return [
            [$this->createMock(ConnectException::class)],
            [$this->createMock(ClientException::class)],
            [$this->createMock(ServerException::class)],
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
