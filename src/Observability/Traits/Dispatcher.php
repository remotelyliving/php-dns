<?php

namespace RemotelyLiving\PHPDNS\Observability\Traits;

use LogicException;
use ReflectionClass;
use ReflectionMethod;
use RemotelyLiving\PHPDNS\Observability\Events\ObservableEventAbstract;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function call_user_func_array;

trait Dispatcher
{
    private ?EventDispatcherInterface $dispatcher = null;

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
        call_user_func_array([$this->getDispatcher(), 'dispatch'], $this->getOrderedDispatcherArguments($event));
    }

    private function getOrderedDispatcherArguments(ObservableEventAbstract $event): array
    {
        $reflection = new ReflectionClass($this->getDispatcher());
        $args = [];

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getName() !== 'dispatch') {
                continue;
            }
            // @codeCoverageIgnoreStart
            foreach ($method->getParameters() as $parameter) {
                $args = ($parameter->getName() === 'event')
                    ? [$event, $event::getName()]
                    : [$event::getName(), $event];
                break;
            }
            // @codeCoverageIgnoreEnd
        }

        return $args;
    }

    private function getDispatcher(): EventDispatcherInterface
    {
        if ($this->dispatcher === null) {
            $this->dispatcher = new EventDispatcher();
        }

        return $this->dispatcher;
    }
}
