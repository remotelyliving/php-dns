<?php declare(strict_types=1);
require_once './vendor/autoload.php';

use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Entities\DNSRecord;
use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Entities\DNSRecordCollection;
use RemotelyLiving\PHPDNS\Mappers\Dig;
use RemotelyLiving\PHPDNS\Observability\Subscribers\STDIOSubscriber;
use RemotelyLiving\PHPDNS\Resolvers\Cached;
use RemotelyLiving\PHPDNS\Resolvers\Chain;
use RemotelyLiving\PHPDNS\Resolvers\CloudFlare;
use RemotelyLiving\PHPDNS\Resolvers\GoogleDNS;
use RemotelyLiving\PHPDNS\Resolvers\LocalSystem;
use Spatie\Dns\Dns;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class_alias(Hostname::class, 'Hostname');
class_alias(DNSRecord::class, 'DNSRecord');
class_alias(DNSRecordType::class, 'DNSRecordType');
class_alias(DNSRecordCollection::class, 'DNSRecordCollection');

$stdOut = new SplFileObject('php://stdout');
$stdErr = new SplFileObject('php://stderr');
$IOSubscriber = new STDIOSubscriber($stdOut, $stdErr);

$localSystemResolver = new LocalSystem();
$localSystemResolver->addSubscriber($IOSubscriber);

$googleDNSResolver = new GoogleDNS();
$googleDNSResolver->addSubscriber($IOSubscriber);

$cloudFlareResolver = new CloudFlare();
$cloudFlareResolver->addSubscriber($IOSubscriber);

$digResolver = new \RemotelyLiving\PHPDNS\Resolvers\Dig(new Dns(), new Dig());
$digResolver->addSubscriber($IOSubscriber);

$chainResolver = new Chain($cloudFlareResolver, $googleDNSResolver, $localSystemResolver);
$cachedResolver = new Cached(new FilesystemAdapter(), $chainResolver);
$cachedResolver->addSubscriber($IOSubscriber);