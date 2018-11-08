<?php
namespace RemotelyLiving\PHPDNS\Observability\Interfaces;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

interface Observable
{
    public function addSubscriber(EventSubscriberInterface $subscriber): void;

    public function addListener(string $eventName, callable $listener, int $priority = 0): void;
}
