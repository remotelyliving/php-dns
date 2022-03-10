<?php
namespace RemotelyLiving\PHPDNS\Tests\Unit\Resolvers;

use Psr\Log\LoggerInterface;
use RemotelyLiving\PHPDNS\Entities\DNSRecord;
use RemotelyLiving\PHPDNS\Entities\DNSRecordCollection;
use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Exceptions\InvalidArgumentException;
use RemotelyLiving\PHPDNS\Mappers\MapperInterface;
use RemotelyLiving\PHPDNS\Observability\Events\DNSQueried;
use RemotelyLiving\PHPDNS\Observability\Events\DNSQueryFailed;
use RemotelyLiving\PHPDNS\Observability\Events\DNSQueryProfiled;
use RemotelyLiving\PHPDNS\Resolvers\Exceptions\QueryFailure;
use RemotelyLiving\PHPDNS\Resolvers\Interfaces\ObservableResolver;
use RemotelyLiving\PHPDNS\Resolvers\ResolverAbstract;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

// @codingStandardsIgnoreFile
class ResolverAbstractTest extends BaseTestAbstract
{
    private \RemotelyLiving\PHPDNS\Resolvers\ResolverAbstract $resolver;

    private \Psr\Log\LoggerInterface $logger;

    private ?\RemotelyLiving\PHPDNS\Resolvers\Exceptions\QueryFailure $error = null;

    private ?\RemotelyLiving\PHPDNS\Entities\DNSRecordCollection $collection = null;

    protected function setUp() : void
    {
        parent::setUp();

        $this->logger = $this->createMock(LoggerInterface::class);
        $this->resolver = new TestResolver($this->collection, $this->error);
    }

    /**
     * @test
     */
    public function isAnObservableResolver() : void
    {
        $this->assertInstanceOf(ResolverAbstract::class, $this->resolver);
        $this->assertInstanceOf(ObservableResolver::class, $this->resolver);
    }

    /**
     * @test
     */
    public function mapsResultsReturnsCollection() : void
    {
        $dnsRecord = DNSRecord::createFromPrimitives('A', 'boop.com', 123);
        $expected = new DNSRecordCollection($dnsRecord);
        $results =[['type' => 'A', 'ip' => '192.168.1.1', 'class' => 'IN']];
        $mapper = $this->createMock(MapperInterface::class);
        $mapper->method('mapFields')
            ->with($results[0])
            ->willReturn($mapper);

        $mapper->method('toDNSRecord')
            ->willReturn($dnsRecord);

        $this->assertEquals($expected, $this->resolver->mapResults($mapper, $results));

    }

    /**
     * @test
     */
    public function mapsResultsAndDiscardsInvalidData() : void
    {
        $expected = new DNSRecordCollection();
        $results =[['type' => 'BAZ', 'ip' => '192.168.1.1', 'class' => 'IN']];
        $mapper = $this->createMock(MapperInterface::class);
        $mapper->method('mapFields')
            ->with($results[0])
            ->willReturn($mapper);

        $mapper->method('toDNSRecord')
            ->willThrowException(new InvalidArgumentException());

        $this->assertEquals($expected, $this->resolver->mapResults($mapper, $results));

    }

    /**
     * @test
     */
    public function dispatchedEventsOnSuccessfulQuery() : void
    {
        $dnsQueried = null;
        $perProfiled = null;

        $this->resolver->addListener(DNSQueried::getName(), function (DNSQueried $event) use (&$dnsQueried) {
            $dnsQueried = $event;
        });

        $this->resolver->addListener(DNSQueryProfiled::getName(), function (DNSQueryProfiled $event) use (&$perProfiled) {
            $perProfiled = $event;
        });

        $this->resolver->getRecords(Hostname::createFromString('facebook.com'));

        $this->assertInstanceOf(DNSQueryProfiled::class, $perProfiled);
        $this->assertInstanceOf(DNSQueried::class, $dnsQueried);
    }

    /**
     * @test
     */
    public function logsSuccessfulEvents() : void
    {
        $this->resolver->setLogger($this->logger);

        $this->logger->expects($this->exactly(2))
            ->method('info')
            ->willReturnCallback(function (string $message, array $context) {
                $this->assertStringStartsWith('DNS', $message);
                $this->assertArrayHasKey('event', $context);
            });

        $this->resolver->getRecords('facebook.com');
    }

    /**
     * @test
     */
    public function logsFailures() : void
    {
        $this->expectException(QueryFailure::class);

        $this->resolver = new TestResolver(null, new QueryFailure());
        $this->resolver->setLogger($this->logger);

        $this->logger->expects($this->once())
            ->method('info')
            ->willReturnCallback(function (string $message, array $context) {
                $this->assertStringStartsWith('DNS', $message);
                $this->assertArrayHasKey('event', $context);
            });

        $this->logger->expects($this->once())
            ->method('error')
            ->willReturnCallback(function (string $message, array $context) {
                $this->assertStringStartsWith('DNS', $message);
                $this->assertArrayHasKey('event', $context);
            });

        $this->resolver->getRecords('facebook.com');
    }

    /**
     * @test
     */
    public function dispatchedEventsOnQueryFailure() : void
    {
        $dnsQueryFailed = null;
        $perProfiled = null;

        $resolver = new TestResolver(null, new QueryFailure());
        $resolver->addListener(DNSQueryFailed::getName(), function (DNSQueryFailed $event) use (&$dnsQueryFailed) {
            $dnsQueryFailed = $event;
        });

        $resolver->addListener(DNSQueryProfiled::getName(), function (DNSQueryProfiled $event) use (&$perProfiled) {
            $perProfiled = $event;
        });

        try {
            $resolver->getRecords(Hostname::createFromString('facebook.com'));
        } catch (QueryFailure) {}

        $this->assertInstanceOf(DNSQueryProfiled::class, $perProfiled);
        $this->assertInstanceOf(DNSQueryFailed::class, $dnsQueryFailed);
    }
}
