<?php
namespace RemotelyLiving\PHPDNS\Resolvers;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
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
        $results = $this->doApiQuery(['name' => (string)$hostname, 'type' => (string)$type]);
        $collection = new DNSRecordCollection();

        foreach ($results as $result) {
            $collection[] = $this->mapper->mapRecord($result)->toDNSRecord();
        }

        return $collection;
    }

    private function doApiQuery(array $query = []): array
    {
        try {
            $response = $this->http->request('GET', '/dns-query?' . http_build_query($query));
        } catch (ClientException | ConnectException $e) {
            throw new QueryFailure("Unable to query CloudFlare API", 0, $e);
        }

        $result = json_decode((string)$response->getBody(), true);

        if (isset($result['Answer'])) {
            return $result['Answer'];
        }

        if (isset($result['Authority'])) {
            return $result['Authority'];
        }

        return [];
    }
}
