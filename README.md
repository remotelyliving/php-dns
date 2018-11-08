[![Build Status](https://travis-ci.org/remotelyliving/php-dns.svg?branch=master)](https://travis-ci.org/remotelyliving/php-dns)
[![Total Downloads](https://poser.pugx.org/remotelyliving/php-dns/downloads)](https://packagist.org/packages/remotelyliving/php-dns)
[![Coverage Status](https://coveralls.io/repos/github/remotelyliving/php-dns/badge.svg?branch=master)](https://coveralls.io/github/remotelyliving/php-dns?branch=master) 
[![License](https://poser.pugx.org/remotelyliving/php-dns/license)](https://packagist.org/packages/remotelyliving/php-dns)

# PHP-DNS: A DNS Abstraction in PHP

### Use Cases

This library might be for you if:

- You want to be able to query DNS records locally or over HTTPS
- You want observability into your DNS lookups
- You want something easy to test / mock in your implementation
- You want to try several different sources of DNS truth
- You want to easily extend it or contribute to get more behavior you want!

### Installation

```sh
composer require remotelyliving/php-dns
```

### Usage

**Basic Resolvers** can be found in [src/Resolvers](https://github.com/remotelyliving/php-dns/tree/master/src/Resolvers)

These resolvers at the least implement the `Resolvers\Interfaces\DNSQuery` interface

- GoogleDNS (uses the GoogleDNS DNS over HTTPS API)
- CloudFlare (uses the CloudFlare DNS over HTTPS API)
- LocalSystem (uses the local PHP dns query function)

```php
$resolver = new Resolvers\GoogleDNS();
$hostname = Hostname::createFromString('google.com');

// can query via convenience methods
$records = $resolver->getARecords($hostname); // returns a collection of DNSRecords

// can also query by any type
$recordType = DNSRecordType::createAAAA();
$moreRecords = $resolver->getRecords($hostname, $recordType);

// can query to see if any resolvers find a record or type.
$resolver->hasRecordType($type) // true / false
$resolver->hasRecord($record) // true / false

This becomes very powerful when used with the Chain Resolver

```

**Chain Resolver**

The Chain Resolver can be used to read through DNS Resolvers until an answer is found.
Whichever you pass in first is the first Resolver it tries in sequence of passing them in.
It implements the same `DNSQuery` interface as the other resolvers.

So something like 

```php
$chainResolver = new Chain($cloudFlareResolver, $googleDNSResolver, $localDNSResolver);
```

And that will call the GoogleDNS Resolver first, if no answer is found it will continue on to the LocalSystem Resolver

You can randomly select which Resolver in the chain it tries first too via `Resolvers\Interfaces\Randomizeable::randomly(): Resolver`
Example:

```php
$foundRecord = $chainResolver->randomly()->getARecords(new Hostname('facebook.com'))->pickFirst();
```

The above code calls through the resolvers randomly until it finds any non empty answer or has exhausted order the chain.

**Cached Resolver**

If you use a PSR6 cache implementation, feel free to wrap whatever Resolver you want to use in the Cached Resolver.
It will take in the TTL of the record(s) average them and use that as the cache TTL.
You may override that behavior by setting a cache TTL in the constructor.

```php
$cachedResolver = new Resolvers\Cached($cache, $resolverOfChoice, $TTL);
```

`Entities\DNSRecordCollection` and `Entities\DNSRecord` are serializable for just such an occasion.

**Entities**

Take a look in the `src/Entities` to see what's available for you to query by and receive.

**Reverse Lookup**

This is offered via a separate `ReverseDNSQuery` interface as it is not common or available for every type of DNS Resolver.
Only the `LocalSystem` Resolver implements it.

### Observability

All provided resolvers have the ability to add subscribers and listeners. They are directly compatible with `symfony/event-dispatcher`

All events can be found here: [src/Observability/Events](https://github.com/remotelyliving/php-dns/tree/master/src/Observability/Events)

With a good idea of what a subscriber can do with them here: [src/Observability/Subscribers](https://github.com/remotelyliving/php-dns/tree/master/src/Observability/Subscribers)

You could decide where you want to stream the events whether its to a log or somewhere else. The events are all safe to `json_encode()` without extra parsing.

If you want to see how easy it is to wire all this up, check out [the repl bootstrap](https://github.com/remotelyliving/php-dns/tree/master/bootstrap/repl.php)

### Tinkering

Take a look in the [Makefile](https://github.com/remotelyliving/php-dns/blob/master/Makefile) for all the things you can do!

There is a very basic REPL implementation that wires up some Resolvers for you already and pipes events to sterr and stdout

`make repl`

```sh
christians-mbp:php-dns chthomas$ make repl
Psy Shell v0.9.9 (PHP 7.2.8 — cli) by Justin Hileman
>>> ls
Variables: $cachedResolver, $chainResolver, $cloudFlareResolver, $googleDNSResolver, $IOSubscriber, $localSystemResolver, $stdErr, $stdOut
>>> $records = $chainResolver->getARecords(new Hostname('facebook.com'))
{
    "dns.query.profiled": {
        "elapsedSeconds": 0.14085698127746582,
        "transactionName": "CloudFlare:facebook.com:A",
        "peakMemoryUsage": 9508312
    }
}
{
    "dns.queried": {
        "resolver": "CloudFlare",
        "hostname": "facebook.com",
        "type": "A",
        "records": [
            {
                "hostname": "facebook.com",
                "type": "A",
                "TTL": 175,
                "class": "IN",
                "IPAddress": "31.13.71.36"
            }
        ],
        "empty": false
    }
}
=> RemotelyLiving\PHPDNS\Entities\DNSRecordCollection {#2375}
>>> $records->pickFirst()
=> RemotelyLiving\PHPDNS\Entities\DNSRecord {#2392}
>>> $googleDNSResolver->hasRecord($records->pickFirst())
{
    "dns.query.profiled": {
        "elapsedSeconds": 0.16751790046691895,
        "transactionName": "GoogleDNS:facebook.com:A",
        "peakMemoryUsage": 9508312
    }
}
{
    "dns.queried": {
        "resolver": "GoogleDNS",
        "hostname": "facebook.com",
        "type": "A",
        "records": [
            {
                "hostname": "facebook.com",
                "type": "A",
                "TTL": 234,
                "class": "IN",
                "IPAddress": "31.13.71.36"
            }
        ],
        "empty": false
    }
}
=> true
>>> 
```