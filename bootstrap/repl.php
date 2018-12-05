<?php
require_once './vendor/autoload.php';

use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Entities\DNSRecord;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\DNSRecordCollection;

class_alias(Hostname::class, 'Hostname');
class_alias(DNSRecord::class, 'DNSRecord');
class_alias(DNSRecordType::class, 'DNSRecordType');
class_alias(DNSRecordCollection::class, 'DNSRecordCollection');

$stdOut = new \SplFileObject('php://stdout');
$stdErr = new \SplFileObject('php://stderr');
$IOSubscriber = new \RemotelyLiving\PHPDNS\Observability\Subscribers\STDIOSubscriber($stdOut, $stdErr);

$localSystemResolver = new \RemotelyLiving\PHPDNS\Resolvers\LocalSystem();
$localSystemResolver->addSubscriber($IOSubscriber);

$googleDNSResolver = new \RemotelyLiving\PHPDNS\Resolvers\GoogleDNS();
$googleDNSResolver->addSubscriber($IOSubscriber);

$cloudFlareResolver = new \RemotelyLiving\PHPDNS\Resolvers\CloudFlare();
$cloudFlareResolver->addSubscriber($IOSubscriber);

$chainResolver = new \RemotelyLiving\PHPDNS\Resolvers\Chain($cloudFlareResolver, $googleDNSResolver, $localSystemResolver);
$cachedResolver = new \RemotelyLiving\PHPDNS\Resolvers\Cached(new \Symfony\Component\Cache\Adapter\FilesystemAdapter(), $chainResolver);
$cachedResolver->addSubscriber($IOSubscriber);