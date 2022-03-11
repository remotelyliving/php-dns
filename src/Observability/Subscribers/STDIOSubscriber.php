<?php

namespace RemotelyLiving\PHPDNS\Observability\Subscribers;

use RemotelyLiving\PHPDNS\Observability\Events\DNSQueried;
use RemotelyLiving\PHPDNS\Observability\Events\DNSQueryFailed;
use RemotelyLiving\PHPDNS\Observability\Events\DNSQueryProfiled;
use RemotelyLiving\PHPDNS\Observability\Events\ObservableEventAbstract;
use SplFileObject;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function json_encode;

final class STDIOSubscriber implements EventSubscriberInterface
{
    public function __construct(private SplFileObject $STDOUT, private SplFileObject $STDERR)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            DNSQueryFailed::getName() => 'onDNSQueryFailed',
            DNSQueried::getName() => 'onDNSQueried',
            DNSQueryProfiled::getName() => 'onDNSQueryProfiled',
        ];
    }

    public function onDNSQueryFailed(ObservableEventAbstract $event): void
    {
        $this->STDERR->fwrite(json_encode($event, JSON_PRETTY_PRINT) . PHP_EOL);
    }

    public function onDNSQueried(ObservableEventAbstract $event): void
    {
        $this->STDOUT->fwrite(json_encode($event, JSON_PRETTY_PRINT) . PHP_EOL);
    }

    public function onDNSQueryProfiled(ObservableEventAbstract $event): void
    {
        $this->STDOUT->fwrite(json_encode($event, JSON_PRETTY_PRINT) . PHP_EOL);
    }
}
