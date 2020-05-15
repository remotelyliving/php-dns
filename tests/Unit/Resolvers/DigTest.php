<?php
namespace RemotelyLiving\PHPDNS\Tests\Unit\Resolvers;

use RemotelyLiving\PHPDNS\Entities;
use RemotelyLiving\PHPDNS\Resolvers;
use RemotelyLiving\PHPDNS\Factories;
use RemotelyLiving\PHPDNS\Tests;
use Spatie;

// @codingStandardsIgnoreFile
class DigTest extends Tests\Unit\BaseTestAbstract
{
    /**
     * @var  RemotelyLiving\PHPDNS\Entities\Hostname;
     */
    private $hostname;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\RemotelyLiving\PHPDNS\Factories\SpatieDNS
     */
    private $spatieDNSFactory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Spatie\Dns\Dns
     */
    private $spatieDNS;

    /**
     * @var \RemotelyLiving\PHPDNS\Resolvers\Dig
     */
    private $dig;

    protected function setUp() : void
    {
        exec('dig', $output, $exit_code);

        if ($exit_code !== 0) {
            $this->markTestSkipped('dig is required to run Dig resolver tests');
        }

        $this->hostname = Entities\Hostname::createFromString('christianthomas.me');
        $this->spatieDNS = $this->createMock(Spatie\Dns\Dns::class);
        $this->spatieDNSFactory = $this->createMock(Factories\SpatieDNS::class);
        $this->spatieDNSFactory->method('createResolver')
            ->with($this->hostname)
            ->willReturn($this->spatieDNS);

        $this->dig = new Resolvers\Dig($this->spatieDNSFactory);
        $this->assertInstanceOf(Resolvers\ResolverAbstract::class, $this->dig);
    }

    public function testDoesQueryForAnyRecords() : void
    {
        $this->spatieDNS->method('getRecords')
            ->with(...Resolvers\Dig::SUPPORTED_QUERY_TYPES)
            ->willReturn(Tests\Fixtures\DigResponses::anyRecords($this->hostname));

        $records = $this->dig->getRecords((string) $this->hostname);
        $this->assertCount(13, $records);
    }

    public function testDoesQueryForSpecificRecords(): void
    {
        $this->spatieDNS->method('getRecords')
            ->with(Entities\DNSRecordType::TYPE_A)
            ->willReturn(Tests\Fixtures\DigResponses::ARecords($this->hostname));

        $records = $this->dig->getARecords((string) $this->hostname);
        $this->assertCount(4, $records);
        foreach ($records as $record) {
            $this->assertTrue($record->getType()->equals(Entities\DNSRecordType::createA()));
        }
    }

    public function testReturnsEmptyCollectionForUnsupportedQueryType(): void
    {
        $this->assertFalse(in_array(Entities\DNSRecordType::TYPE_PTR, Resolvers\Dig::SUPPORTED_QUERY_TYPES));
        $this->assertTrue(
            $this->dig->getRecords($this->hostname, Entities\DNSRecordType::TYPE_PTR)->isEmpty()
        );
    }

    public function testHandlesEmptyResponse(): void
    {
        $this->spatieDNS->method('getRecords')
            ->with(...Resolvers\Dig::SUPPORTED_QUERY_TYPES)
            ->willReturn(Tests\Fixtures\DigResponses::empty());

        $this->assertFalse(in_array(Entities\DNSRecordType::TYPE_PTR, Resolvers\Dig::SUPPORTED_QUERY_TYPES));
        $this->assertTrue(
            $this->dig->getRecords($this->hostname)->isEmpty()
        );
    }

    public function testHandlesSpatieExceptionAndRethrowsAsQueryFailure(): void
    {
        $this->expectException(Resolvers\Exceptions\QueryFailure::class);
        $this->expectExceptionMessage('The message');

        $this->spatieDNS->method('getRecords')
            ->with(...Resolvers\Dig::SUPPORTED_QUERY_TYPES)
            ->willThrowException(new \InvalidArgumentException('The message'));

        $this->dig->getRecords($this->hostname);
    }
}
