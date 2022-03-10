<?php

namespace RemotelyLiving\PHPDNS\Tests\Unit\Resolvers;

use RemotelyLiving\PHPDNS\Entities\DNSRecord;
use RemotelyLiving\PHPDNS\Entities\DNSRecordCollection;
use RemotelyLiving\PHPDNS\Entities\Interfaces\DNSRecordInterface;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Resolvers\Chain;
use RemotelyLiving\PHPDNS\Resolvers\Exceptions\QueryFailure;
use RemotelyLiving\PHPDNS\Resolvers\Interfaces\ObservableResolver;
use RemotelyLiving\PHPDNS\Resolvers\Interfaces\Resolver;
use RemotelyLiving\PHPDNS\Resolvers\ResolverAbstract;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ChainTest extends BaseTestAbstract
{
    private \RemotelyLiving\PHPDNS\Resolvers\Interfaces\Resolver $resolver1;

    private \RemotelyLiving\PHPDNS\Resolvers\Interfaces\Resolver $resolver2;

    private \RemotelyLiving\PHPDNS\Resolvers\Chain $chainResolver;

    protected function setUp(): void
    {
        $this->resolver1 = $this->createMock(ObservableResolver::class);
        $this->resolver2 = $this->createMock(ObservableResolver::class);

        $this->chainResolver = new Chain($this->resolver1, $this->resolver2);
        $this->assertInstanceOf(ResolverAbstract::class, $this->chainResolver);
    }

    /**
     * @test
     */
    public function randomizesOrderOfChain(): void
    {
        $this->assertNotSame($this->chainResolver, $this->chainResolver->randomly());
    }

    /**
     * @test
     */
    public function pushesSubscribersAndObserversToObservableChainResolvers(): void
    {
        $subscriber = $this->createMock(EventSubscriberInterface::class);
        $callable = function () {
        };
        $this->resolver1->expects($this->once())
            ->method('addSubscriber')
            ->with($subscriber);

        $this->resolver1->expects($this->once())
            ->method('addListener')
            ->with('name', $callable, 2);

        $this->resolver2->expects($this->once())
            ->method('addSubscriber')
            ->with($subscriber);

        $this->resolver2->expects($this->once())
            ->method('addListener')
            ->with('name', $callable, 2);

        $nonObservableResolver = $this->createMock(Resolver::class);

        $this->chainResolver->pushResolver($nonObservableResolver);

        $this->chainResolver->addSubscriber($subscriber);
        $this->chainResolver->addListener('name', $callable, 2);
    }

    /**
     * @test
     */
    public function hasRecordFallsThroughToFalse(): void
    {
        $record = $this->createMock(DNSRecordInterface::class);

        $this->resolver1->method('hasRecord')
            ->with($record)
            ->willReturn(false);

        $this->resolver2->expects($this->once())
            ->method('hasRecord')
            ->with($record)
            ->willReturn(false);

        $this->assertFalse($this->chainResolver->hasRecord($record));
    }

    /**
     * @test
     */
    public function hasOrDoesNotHaveRecord(): void
    {
        $record = $this->createMock(DNSRecordInterface::class);

        $this->resolver1->method('hasRecord')
            ->with($record)
            ->willReturn(false);

        $this->resolver2
            ->method('hasRecord')
            ->with($record)
            ->willReturn(true);

        $this->assertTrue($this->chainResolver->hasRecord($record));
    }

    /**
     * @test
     */
    public function letsQueryErrorsContinue(): void
    {
        $record = DNSRecord::createFromPrimitives('A', 'facebook.com', 123);
        $hostname = Hostname::createFromString('facebook.com');
        $type = DNSRecordType::createFromString('A');

        $this->resolver1->method('getRecords')
            ->with($hostname, $type)
            ->willThrowException(new QueryFailure());

        $this->resolver2->method('getRecords')
            ->with($hostname, $type)
            ->willReturn(new DNSRecordCollection($record));

        $this->assertEquals($record, $this->chainResolver->getARecords($hostname)->pickFirst());
    }

    /**
     * @test
     */
    public function letsQueryErrorsContinueAndFallsThroughToEmptyCollection(): void
    {
        $hostname = Hostname::createFromString('facebook.com');
        $type = DNSRecordType::createFromString('A');

        $this->resolver1->method('getRecords')
            ->with($hostname, $type)
            ->willThrowException(new QueryFailure());

        $this->resolver2->method('getRecords')
            ->with($hostname, $type)
            ->willReturn(new DNSRecordCollection());

        $this->assertEquals(new DNSRecordCollection(), $this->chainResolver->getARecords($hostname));
    }

    /**
     * @test
     */
    public function doesQueryOnResolversUntilAnswerIsFoundAsDefaultBehavior(): void
    {
        $expectedCollection = new DNSRecordCollection(DNSRecord::createFromPrimitives('TXT', 'twitter.com', 123));
        $hostname = Hostname::createFromString('twitter.com');
        $type = DNSRecordType::createTXT();

        $this->resolver1->method('getRecords')
            ->with($hostname, $type)
            ->willReturn(new DNSRecordCollection());

        $this->resolver2->method('getRecords')
            ->with($hostname, $type)
            ->willReturn($expectedCollection);

        $this->assertEquals($expectedCollection, $this->chainResolver->getRecords($hostname, $type));
        $this->assertEquals(
            $expectedCollection,
            $this->chainResolver->withFirstResults()->getRecords($hostname, $type)
        );
    }

    /**
     * @test
     */
    public function doesQueryOnResolversUntilAllAnswersAreFound(): void
    {
        $mxRecord = DNSRecord::createFromPrimitives('TXT', 'twitter.com', 123);
        $aRecord = DNSRecord::createFromPrimitives('A', 'twitter.com', 321, '192.168.1.1');

        $expectedCollection1 = new DNSRecordCollection($mxRecord);
        $expectedCollection2 = new DNSRecordCollection($aRecord);

        $hostname = Hostname::createFromString('twitter.com');
        $type = DNSRecordType::createANY();

        $this->resolver1->method('getRecords')
            ->with($hostname, $type)
            ->willReturn($expectedCollection1);

        $this->resolver2->method('getRecords')
            ->with($hostname, $type)
            ->willReturn($expectedCollection2);

        $this->assertEquals(
            new DNSRecordCollection($mxRecord, $aRecord),
            $this->chainResolver->withAllResults()->getRecords($hostname, $type)
        );
    }

    /**
     * @test
     */
    public function doesQueryOnResolversAndOnlyConsensusRecordsAreFound(): void
    {
        $mxRecord = DNSRecord::createFromPrimitives('TXT', 'twitter.com', 123);
        $aRecord = DNSRecord::createFromPrimitives(
            'A',
            'twitter.com',
            321,
            '192.168.1.1'
        );

        $expectedCollection1 = new DNSRecordCollection($mxRecord, $aRecord);
        $expectedCollection2 = new DNSRecordCollection($aRecord);

        $hostname = Hostname::createFromString('twitter.com');
        $type = DNSRecordType::createANY();

        $this->resolver1->method('getRecords')
            ->with($hostname, $type)
            ->willReturn($expectedCollection1);

        $this->resolver2->method('getRecords')
            ->with($hostname, $type)
            ->willReturn($expectedCollection2);

        $this->assertEquals(
            new DNSRecordCollection($aRecord),
            $this->chainResolver->withConsensusResults()->getRecords($hostname, $type)
        );
    }
}
