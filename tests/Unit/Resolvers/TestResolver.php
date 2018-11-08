<?php
namespace RemotelyLiving\PHPDNS\Tests\Unit\Resolvers;

use RemotelyLiving\PHPDNS\Entities\DNSRecordCollection;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Resolvers\Exceptions\QueryFailure;
use RemotelyLiving\PHPDNS\Resolvers\ResolverAbstract;

class TestResolver extends ResolverAbstract
{
    /**
     * @var \RemotelyLiving\PHPDNS\Entities\DNSRecordCollection
     */
    private $recordCollection;

    /**
     * @var \RemotelyLiving\PHPDNS\Resolvers\Exceptions\QueryFailure
     */
    private $error;

    public function __construct(DNSRecordCollection $recordCollection = null, QueryFailure $error = null)
    {
        $this->recordCollection = $recordCollection;
        $this->error = $error;
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
