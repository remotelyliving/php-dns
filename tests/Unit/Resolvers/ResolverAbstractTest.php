<?php
namespace RemotelyLiving\PHPDNS\Tests\Unit\Resolvers;

use Psr\Log\LoggerInterface;
use RemotelyLiving\PHPDNS\Entities\Hostname;
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
    /**
     * @var \RemotelyLiving\PHPDNS\Resolvers\ResolverAbstract
     */
    private $resolver;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \RemotelyLiving\PHPDNS\Resolvers\Exceptions\QueryFailure|null
     */
    private $error = null;

    /**
     * @var \RemotelyLiving\PHPDNS\Entities\DNSRecordCollection|null
     */
    private $collection = null;

    protected function setUp()
    {
        parent::setUp();

        $this->logger = $this->createMock(LoggerInterface::class);
        $this->resolver = new TestResolver($this->collection, $this->error);
    }

    /**
     * @test
     */
    public function isAnObservableResolver()
    {
        $this->assertInstanceOf(ResolverAbstract::class, $this->resolver);
        $this->assertInstanceOf(ObservableResolver::class, $this->resolver);
    }

    /**
     * @test
     */
    public function dispatchedEventsOnSuccessfulQuery()
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
    public function logsSuccessfulEvents()
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
     * @expectedException \RemotelyLiving\PHPDNS\Resolvers\Exceptions\QueryFailure
     */
    public function logsFailures()
    {
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
    public function dispatchedEventsOnQueryFailure()
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
        } catch (QueryFailure $e) {}

        $this->assertInstanceOf(DNSQueryProfiled::class, $perProfiled);
        $this->assertInstanceOf(DNSQueryFailed::class, $dnsQueryFailed);
    }
}
