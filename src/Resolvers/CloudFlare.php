<?php
namespace RemotelyLiving\PHPDNS\Resolvers;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use function GuzzleHttp\Promise\unwrap;
use GuzzleHttp\Psr7\Response;
use RemotelyLiving\PHPDNS\Entities\DNSRecordCollection;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Mappers\CloudFlare as CloudFlareMapper;
use RemotelyLiving\PHPDNS\Resolvers\Exceptions\QueryFailure;

class CloudFlare extends ResolverAbstract
{
    protected const BASE_URI = 'https://cloudflare-dns.com';
    protected const DEFAULT_TIMEOUT = 5.0;
    protected const DEFAULT_OPTIONS = [
        'base_uri' => self::BASE_URI,
        'connect_timeout' => self::DEFAULT_TIMEOUT,
        'strict' => true,
        'allow_redirects' => false,
        'protocols' => ['https'],
        'headers' => [
            'Accept' => 'application/dns-json',
        ],
    ];

    /**
     * @var \GuzzleHttp\Client|\GuzzleHttp\ClientInterface
     */
    private $http;

    /**
     * @var \RemotelyLiving\PHPDNS\Mappers\CloudFlare
     */
    private $mapper;

    public function __construct(ClientInterface $http = null, CloudFlareMapper $mapper = null)
    {
        $this->http = $http ?? new Client(self::DEFAULT_OPTIONS);
        $this->mapper = $mapper ?? new CloudFlareMapper();
    }

    protected function doQuery(Hostname $hostname, DNSRecordType $type): DNSRecordCollection
    {
        try {
            return (!$type->isA(DNSRecordType::TYPE_ANY))
                ? $this->doApiQuery(['name' => (string)$hostname, 'type' => (string)$type])
                : $this->doAnyApiQuery($hostname);
        } catch (RequestException $e) {
            throw new QueryFailure("Unable to query CloudFlare API", 0, $e);
        }
    }

    /**
     * Cloudflare does not support ANY queries, so we must ask for all record types individually
     */
    private function doAnyApiQuery(Hostname $hostname) : DNSRecordCollection
    {
        $collection = new DNSRecordCollection();
        $promises = [];

        foreach (DNSRecordType::VALID_TYPES as $type) {
            if ($type === DNSRecordType::TYPE_ANY) {
                continue;
            }

            $promises[] = $this->doAsyncApiQuery(['name' => (string)$hostname, 'type' => $type])
                ->then(function (Response $response) use (&$collection) {
                    $decoded = json_decode((string)$response->getBody(), true);
                    foreach ($this->parseResult($decoded) as $fields) {
                        $collection[] = $this->mapper->mapFields($fields)->toDNSRecord();
                    }
                });
        }

        unwrap($promises);

        return $collection;
    }

    private function doAsyncApiQuery(array $query) : PromiseInterface
    {
        return $this->http->requestAsync('GET', '/dns-query?' . http_build_query($query));
    }

    private function doApiQuery(array $query = []) : DNSRecordCollection
    {
        $decoded = json_decode((string)$this->doAsyncApiQuery($query)->wait()->getBody(), true);
        $collection = new DNSRecordCollection();

        foreach ($this->parseResult($decoded) as $fields) {
            $collection[] = $this->mapper->mapFields($fields)->toDNSRecord();
        }

        return $collection;
    }

    private function parseResult(array $result) : array
    {
        if (isset($result['Answer'])) {
            return $result['Answer'];
        }

        if (isset($result['Authority'])) {
            return $result['Authority'];
        }

        return [];
    }
}
