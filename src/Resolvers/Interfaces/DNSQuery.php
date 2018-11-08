<?php
namespace RemotelyLiving\PHPDNS\Resolvers\Interfaces;

use RemotelyLiving\PHPDNS\Entities\DNSRecord;
use RemotelyLiving\PHPDNS\Entities\DNSRecordCollection;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Hostname;

interface DNSQuery
{
    /**
     * @param \RemotelyLiving\PHPDNS\Entities\Hostname $hostname
     *
     * @return \RemotelyLiving\PHPDNS\Entities\DNSRecordCollection
     *
     * @throws \RemotelyLiving\PHPDNS\Resolvers\Exceptions\QueryFailure
     */
    public function getARecords(Hostname $hostname): DNSRecordCollection;

    /**
     * @param \RemotelyLiving\PHPDNS\Entities\Hostname $hostname
     *
     * @return \RemotelyLiving\PHPDNS\Entities\DNSRecordCollection
     *
     * @throws \RemotelyLiving\PHPDNS\Resolvers\Exceptions\QueryFailure
     */
    public function getAAAARecords(Hostname $hostname): DNSRecordCollection;

    /**
     * @param \RemotelyLiving\PHPDNS\Entities\Hostname $hostname
     *
     * @return \RemotelyLiving\PHPDNS\Entities\DNSRecordCollection
     *
     * @throws \RemotelyLiving\PHPDNS\Resolvers\Exceptions\QueryFailure
     */
    public function getCNAMERecords(Hostname $hostname): DNSRecordCollection;

    /**
     * @param \RemotelyLiving\PHPDNS\Entities\Hostname $hostname
     *
     * @return \RemotelyLiving\PHPDNS\Entities\DNSRecordCollection
     *
     * @throws \RemotelyLiving\PHPDNS\Resolvers\Exceptions\QueryFailure
     */
    public function getTXTRecords(Hostname $hostname): DNSRecordCollection;

    /**
     * @param \RemotelyLiving\PHPDNS\Entities\Hostname $hostname
     *
     * @return \RemotelyLiving\PHPDNS\Entities\DNSRecordCollection
     *
     * @throws \RemotelyLiving\PHPDNS\Resolvers\Exceptions\QueryFailure
     */
    public function getMXRecords(Hostname $hostname): DNSRecordCollection;

    /**
     * @param \RemotelyLiving\PHPDNS\Entities\Hostname $hostname
     *
     * @return \RemotelyLiving\PHPDNS\Entities\DNSRecordCollection
     *
     * @throws \RemotelyLiving\PHPDNS\Resolvers\Exceptions\QueryFailure
     */
    public function getRecords(Hostname $hostname, DNSRecordType $recordType = null): DNSRecordCollection;

    /**
     * @param \RemotelyLiving\PHPDNS\Entities\Hostname $hostname
     * @param \RemotelyLiving\PHPDNS\Entities\DNSRecordType $recordType
     *
     * @return bool
     *
     * @throws \RemotelyLiving\PHPDNS\Resolvers\Exceptions\QueryFailure
     */
    public function recordTypeExists(Hostname $hostname, DNSRecordType $recordType): bool;

    /**
     * @param \RemotelyLiving\PHPDNS\Entities\DNSRecord $record
     *
     * @return bool
     *
     * @throws \RemotelyLiving\PHPDNS\Resolvers\Exceptions\QueryFailure
     */
    public function hasRecord(DNSRecord $record): bool;
}
