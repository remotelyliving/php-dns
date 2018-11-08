<?php
namespace RemotelyLiving\PHPDNS\Resolvers;

use RemotelyLiving\PHPDNS\Entities\DNSRecord;
use RemotelyLiving\PHPDNS\Entities\DNSRecordCollection;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Observability\Events\DNSQueried;
use RemotelyLiving\PHPDNS\Observability\Events\DNSQueryFailed;
use RemotelyLiving\PHPDNS\Observability\Events\DNSQueryProfiled;
use RemotelyLiving\PHPDNS\Observability\Traits\Dispatcher;
use RemotelyLiving\PHPDNS\Observability\Traits\Profileable;
use RemotelyLiving\PHPDNS\Resolvers\Exceptions\QueryFailure;
use RemotelyLiving\PHPDNS\Resolvers\Interfaces\ObservableResolver;

abstract class ResolverAbstract implements ObservableResolver
{
    use Dispatcher,
        Profileable;

    private $name = null;

    public function getName(): string
    {
        if ($this->name === null) {
            $explodedClass = explode('\\', get_class($this));
            $this->name = (string) array_pop($explodedClass);
        }

        return $this->name;
    }

    public function getARecords(Hostname $hostname): DNSRecordCollection
    {
        return $this->getRecords($hostname, DNSRecordType::createA());
    }

    public function getAAAARecords(Hostname $hostname): DNSRecordCollection
    {
        return $this->getRecords($hostname, DNSRecordType::createAAAA());
    }

    public function getCNAMERecords(Hostname $hostname): DNSRecordCollection
    {
        return $this->getRecords($hostname, DNSRecordType::createCNAME());
    }

    public function getTXTRecords(Hostname $hostname): DNSRecordCollection
    {
        return $this->getRecords($hostname, DNSRecordType::createTXT());
    }

    public function getMXRecords(Hostname $hostname): DNSRecordCollection
    {
        return $this->getRecords($hostname, DNSRecordType::createMX());
    }

    public function recordTypeExists(Hostname $hostname, DNSRecordType $recordType): bool
    {
        return !$this->getRecords($hostname, $recordType)->isEmpty();
    }

    public function hasRecord(DNSRecord $record): bool
    {
        return $this->getRecords($record->getHostname(), $record->getType())
            ->has($record);
    }

    public function getRecords(Hostname $hostname, DNSRecordType $recordType = null): DNSRecordCollection
    {
        $any = DNSRecordType::createANY();
        $recordType = $recordType ?? $any;
        $profile = $this->createProfile("{$this->getName()}:{$hostname}:{$recordType}");
        $profile->startTransaction();

        try {
            $result = ($recordType->equals($any))
                ? $this->doQuery($hostname, $recordType)
                : $this->doQuery($hostname, $recordType)->filteredByType($recordType);
        } catch (QueryFailure $e) {
            $this->dispatch(new DNSQueryFailed($this, $hostname, $recordType, $e));
            throw $e;
        } finally {
            $profile->endTransaction();
            $profile->samplePeakMemoryUsage();
            $this->dispatch(new DNSQueryProfiled($profile));
        }

        $this->dispatch(new DNSQueried($this, $hostname, $recordType, $result));

        return $result;
    }

    abstract protected function doQuery(Hostname $hostname, DNSRecordType $recordType): DNSRecordCollection;
}
