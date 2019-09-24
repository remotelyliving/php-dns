<?php
namespace RemotelyLiving\PHPDNS\Resolvers;

use RemotelyLiving\PHPDNS\Entities\DNSRecord;
use RemotelyLiving\PHPDNS\Entities\DNSRecordCollection;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Exceptions\InvalidArgumentException;
use RemotelyLiving\PHPDNS\Mappers\MapperInterface;
use RemotelyLiving\PHPDNS\Observability\Events\DNSQueried;
use RemotelyLiving\PHPDNS\Observability\Events\DNSQueryFailed;
use RemotelyLiving\PHPDNS\Observability\Events\DNSQueryProfiled;
use RemotelyLiving\PHPDNS\Observability\Traits\Dispatcher;
use RemotelyLiving\PHPDNS\Observability\Traits\Logger;
use RemotelyLiving\PHPDNS\Observability\Traits\Profileable;
use RemotelyLiving\PHPDNS\Resolvers\Exceptions\QueryFailure;
use RemotelyLiving\PHPDNS\Resolvers\Interfaces\ObservableResolver;

abstract class ResolverAbstract implements ObservableResolver
{
    use Logger,
        Dispatcher,
        Profileable;

    /**
     * @var string|null
     */
    private $name = null;

    public function getName(): string
    {
        if ($this->name === null) {
            $explodedClass = explode('\\', get_class($this));
            $this->name = (string)array_pop($explodedClass);
        }

        return $this->name;
    }

    public function getARecords(string $hostname): DNSRecordCollection
    {
        return $this->getRecords($hostname, (string)DNSRecordType::createA());
    }

    public function getAAAARecords(string $hostname): DNSRecordCollection
    {
        return $this->getRecords($hostname, (string)DNSRecordType::createAAAA());
    }

    public function getCNAMERecords(string $hostname): DNSRecordCollection
    {
        return $this->getRecords($hostname, (string)DNSRecordType::createCNAME());
    }

    public function getTXTRecords(string $hostname): DNSRecordCollection
    {
        return $this->getRecords($hostname, (string)DNSRecordType::createTXT());
    }

    public function getMXRecords(string $hostname): DNSRecordCollection
    {
        return $this->getRecords($hostname, (string)DNSRecordType::createMX());
    }

    public function recordTypeExists(string $hostname, string $recordType): bool
    {
        return !$this->getRecords($hostname, $recordType)->isEmpty();
    }

    public function hasRecord(DNSRecord $record): bool
    {
        return $this->getRecords((string)$record->getHostname(), (string)$record->getType())
            ->has($record);
    }

    public function getRecords(string $hostname, string $recordType = null): DNSRecordCollection
    {
        $recordType = DNSRecordType::createFromString($recordType ?? 'ANY');
        $hostname = Hostname::createFromString($hostname);

        $profile = $this->createProfile("{$this->getName()}:{$hostname}:{$recordType}");
        $profile->startTransaction();

        try {
            $result = ($recordType->equals(DNSRecordType::createANY()))
                ? $this->doQuery($hostname, $recordType)
                : $this->doQuery($hostname, $recordType)->filteredByType($recordType);
        } catch (QueryFailure $e) {
            $dnsQueryFailureEvent = new DNSQueryFailed($this, $hostname, $recordType, $e);
            $this->dispatch($dnsQueryFailureEvent);
            $this->getLogger()->error(
                'DNS query failed',
                ['event' => json_encode($dnsQueryFailureEvent), 'exception' => $e]
            );

            throw $e;
        } finally {
            $profile->endTransaction();
            $profile->samplePeakMemoryUsage();
            $dnsQueryProfiledEvent = new DNSQueryProfiled($profile);
            $this->dispatch($dnsQueryProfiledEvent);
            $this->getLogger()->info('DNS query profiled', ['event' => json_encode($dnsQueryProfiledEvent)]);
        }

        $dnsQueriedEvent = new DNSQueried($this, $hostname, $recordType, $result);
        $this->dispatch($dnsQueriedEvent);
        $this->getLogger()->info('DNS queried', ['event' => json_encode($dnsQueriedEvent)]);
        return $result;
    }

    public function mapResults(MapperInterface $mapper, array $results): DNSRecordCollection
    {
        $collection = new DNSRecordCollection();
        array_map(function (array $fields) use (&$collection, $mapper) {
            try {
                $collection[] = $mapper->mapFields($fields)->toDNSRecord();
            } catch (InvalidArgumentException $e) {
                $this->getLogger()->warning('Invalid fields passed to mapper', $fields);
            }
        }, $results);

        return $collection;
    }

    abstract protected function doQuery(Hostname $hostname, DNSRecordType $recordType): DNSRecordCollection;
}
