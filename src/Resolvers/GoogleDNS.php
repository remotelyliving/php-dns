<?php

namespace RemotelyLiving\PHPDNS\Resolvers;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\TransferException;
use RemotelyLiving\PHPDNS\Entities\DNSRecord;
use RemotelyLiving\PHPDNS\Entities\DNSRecordCollection;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Mappers\GoogleDNS as GoogleDNSMapper;
use RemotelyLiving\PHPDNS\Resolvers\Exceptions\QueryFailure;

class GoogleDNS extends ResolverAbstract
{
    protected const BASE_URI = 'https://dns.google.com';
    protected const DEFAULT_TIMEOUT = 5.0;
    protected const DEFAULT_OPTIONS = [
        'base_uri' => self::BASE_URI,
        'strict' => true,
        'allow_redirects' => false,
        'connect_timeout' => self::DEFAULT_TIMEOUT,
        'protocols' => ['https'],
        'headers' => [
            'Accept' => 'application/json',
        ],
    ];

    /**
     * @var \GuzzleHttp\Client|\GuzzleHttp\ClientInterface
     */
    private $http;

    /**
     * @var \RemotelyLiving\PHPDNS\Mappers\GoogleDNS
     */
    private $mapper;

    /**
     * @var int
     */
    private $consensusAttempts;

    public function __construct(
        ClientInterface $http = null,
        GoogleDNSMapper $mapper = null,
        int $consensusAttempts = 3
    ) {
        $this->http = $http ?? new Client(self::DEFAULT_OPTIONS);
        $this->mapper = $mapper ?? new GoogleDNSMapper();
        $this->consensusAttempts = $consensusAttempts;
    }

    /**
     * Google DNS has consistency issues so this tries a few times to get an answer
     */
    public function hasRecord(DNSRecord $record): bool
    {
        $attempts = 0;

        do {
            $hasRecord = $this->getRecords((string)$record->getHostname(), (string)$record->getType())
                ->has($record);

            ++$attempts;
        } while ($hasRecord === false && $attempts < $this->consensusAttempts);

        return $hasRecord;
    }

    protected function doQuery(Hostname $hostname, DNSRecordType $type): DNSRecordCollection
    {
        $results = $this->doApiQuery(['name' => (string)$hostname, 'type' => (string)$type]);

        return $this->mapResults($this->mapper, $results);
    }

    /**
     * @throws \RemotelyLiving\PHPDNS\Resolvers\Exceptions\QueryFailure
     */
    private function doApiQuery(array $query = []): array
    {
        try {
            $response = $this->http->request('GET', '/resolve?' . http_build_query($query));
        } catch (TransferException $e) {
            throw new QueryFailure("Unable to query GoogleDNS API", 0, $e);
        }

        $result = (array) json_decode((string)$response->getBody(), true);

        if (isset($result['Answer'])) {
            return $result['Answer'];
        }

        if (isset($result['Authority'])) {
            return $result['Authority'];
        }

        return [];
    }
}
