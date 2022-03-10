<?php

namespace RemotelyLiving\PHPDNS\Resolvers;

use RemotelyLiving\PHPDNS\Entities\DNSRecordCollection;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Factories\SpatieDNS;
use RemotelyLiving\PHPDNS\Mappers\Dig as DigMapper;
use RemotelyLiving\PHPDNS\Resolvers\Exceptions\QueryFailure;
use Spatie\Dns\Dns;
use Spatie\Dns\Records\Record;
use Throwable;

use function array_slice;
use function explode;
use function implode;
use function preg_replace;
use function trim;

final class Dig extends ResolverAbstract
{
    public const SUPPORTED_QUERY_TYPES = [
        DNSRecordType::TYPE_A,
        DNSRecordType::TYPE_AAAA,
        DNSRecordType::TYPE_CNAME,
        DNSRecordType::TYPE_NS,
        DNSRecordType::TYPE_SOA,
        DNSRecordType::TYPE_MX,
        DNSRecordType::TYPE_SRV,
        DNSRecordType::TYPE_TXT,
        DNSRecordType::TYPE_CAA,
    ];

    private Dns $dig;

    private DigMapper $mapper;

    public function __construct(
        Dns $dig = null,
        DigMapper $mapper = null,
        Hostname $nameserver = null
    ) {
        $this->dig = $dig ?? new Dns();

        if ($nameserver !== null) {
            $this->dig = $this->dig->useNameserver((string) $nameserver);
        }

        $this->mapper = $mapper ?? new DigMapper();
    }

    protected function doQuery(Hostname $hostname, DNSRecordType $recordType): DNSRecordCollection
    {
        if (!self::isSupportedQueryType($recordType)) {
            return new DNSRecordCollection();
        }

        try {
            $response = ($recordType->equals(DNSRecordType::createANY()))
                ? $this->dig->getRecords((string) $hostname, self::SUPPORTED_QUERY_TYPES)
                : $this->dig->getRecords((string) $hostname, (string) $recordType);
        } catch (Throwable $e) {
            throw new QueryFailure($e->getMessage(), 0, $e);
        }

        return $this->mapResults($this->mapper, array_map(fn(Record $record): array => $record->toArray(), $response));
    }

    private static function isSupportedQueryType(DNSRecordType $type): bool
    {
        if ($type->isA(DNSRecordType::TYPE_ANY)) {
            return true;
        }

        foreach (self::SUPPORTED_QUERY_TYPES as $queryType) {
            if ($type->isA($queryType)) {
                return true;
            }
        }

        return false;
    }
}
