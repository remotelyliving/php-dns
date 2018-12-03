<?php
namespace RemotelyLiving\PHPDNS\Observability\Traits;

use RemotelyLiving\PHPDNS\Observability\Events\ObservableEventAbstract;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

trait Dispatcher
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface|null
     */
    private $dispatcher = null;

    public function setDispatcher(EventDispatcherInterface $dispatcher): void
    {
        $this->dispatcher = $dispatcher;
    }

    public function addSubscriber(EventSubscriberInterface $subscriber): void
    {
        $this->getDispatcher()->addSubscriber($subscriber);
    }

    public function addListener(string $eventName, callable $listener, int $priority = 0): void
    {
        $this->getDispatcher()->addListener($eventName, $listener, $priority);
    }

    public function dispatch(ObservableEventAbstract $event): void
    {
        $this->getDispatcher()->dispatch($event::getName(), $event);
    }

    private function getDispatcher(): EventDispatcherInterface
    {
        if ($this->dispatcher === null) {
            $this->dispatcher = new EventDispatcher();
        }

        return $this->dispatcher;
    }
}
