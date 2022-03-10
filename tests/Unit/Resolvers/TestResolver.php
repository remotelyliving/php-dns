<?php

namespace RemotelyLiving\PHPDNS\Tests\Unit\Resolvers;

use RemotelyLiving\PHPDNS\Entities\DNSRecordCollection;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Resolvers\Exceptions\QueryFailure;
use RemotelyLiving\PHPDNS\Resolvers\ResolverAbstract;

class TestResolver extends ResolverAbstract
{
    public function __construct(private ?DNSRecordCollection $recordCollection, private ?QueryFailure $error)
    {
    }

    protected function doQuery(Hostname $hostname, DNSRecordType $recordType): DNSRecordCollection
    {
        if ($this->recordCollection) {
            return $this->recordCollection;
        }

        if ($this->error) {
            throw $this->error;
        }

        return new DNSRecordCollection();
    }
}
