<?php

namespace RemotelyLiving\PHPDNS\Resolvers;

use RemotelyLiving\PHPDNS\Entities\DNSRecordCollection;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Factories\SpatieDNS;
use RemotelyLiving\PHPDNS\Mappers\Dig as DigMapper;
use RemotelyLiving\PHPDNS\Resolvers\Exceptions\QueryFailure;

class Dig extends ResolverAbstract
{
    private const SUPPORTED_QUERY_TYPES = [
        DNSRecordType::TYPE_A,
        DNSRecordType::TYPE_AAAA,
        DNSRecordType::TYPE_CNAME,
        DNSRecordType::TYPE_NS,
        DNSRecordType::TYPE_SOA,
        DNSRecordType::TYPE_MX,
        DNSRecordType::TYPE_SRV,
        DNSRecordType::TYPE_TXT,
        DNSRecordType::TYPE_CAA,
        DNSRecordType::TYPE_NAPTR,
    ];

    /**
     * @var \RemotelyLiving\PHPDNS\Factories\SpatieDNS
     */
    private $spatieDNSFactory;

    /**
     * @var \RemotelyLiving\PHPDNS\Mappers\Dig
     */
    private $mapper;

    /**
     * @var \RemotelyLiving\PHPDNS\Entities\Hostname|null
     */
    private $nameserver;

    public function __construct(
        SpatieDNS $spatieDNSFactory = null,
        DigMapper $mapper = null,
        Hostname $nameserver = null
    ) {
        $this->spatieDNSFactory = $spatieDNSFactory ?? new SpatieDNS();
        $this->mapper = $mapper ?? new DigMapper();
        $this->nameserver = $nameserver;
    }

    protected function doQuery(Hostname $hostname, DNSRecordType $type): DNSRecordCollection
    {
        if (!self::isSupportedQueryType($type)) {
            return new DNSRecordCollection();
        }

        $dig = $this->spatieDNSFactory->createResolver($hostname, $this->nameserver);
        try {
            $response = ($type->equals(DNSRecordType::createANY()))
                ? $dig->getRecords(...self::SUPPORTED_QUERY_TYPES)
                : $dig->getRecords((string) $type);
        } catch (\Throwable $e) {
            throw new QueryFailure($e->getMessage(), 0, $e);
        }

        return $this->mapResults($this->mapper, self::parseDigResponseToRows($response));
    }

    private static function parseDigResponseToRows(string $digResponse): array
    {
        $rows = [];
        foreach (explode(PHP_EOL, self::normalizeColumns($digResponse)) as $line) {
            if (!trim($line)) {
                continue;
            }

            $columns = explode(' ', $line);
            $rows[] = [$columns[0], $columns[1], $columns[2], $columns[3], implode(' ', array_slice($columns, 4))];
        }

        return $rows;
    }

    private static function normalizeColumns(string $response): string
    {
        $keysRemoved = preg_replace('/;(.*)/m', ' ', trim($response));
        $tabsRemoved = preg_replace('/(\t)/i', ' ', (string) $keysRemoved);
        $breaksRemoved = preg_replace('/\s\s/m', '', (string) $tabsRemoved);
        return (string) preg_replace('/(\(\s|(\s\)))/m', '', (string) $breaksRemoved);
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
