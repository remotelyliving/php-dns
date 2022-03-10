<?php

namespace RemotelyLiving\PHPDNS\Tests\Unit\Observability\Traits;

use RemotelyLiving\PHPDNS\Observability\Events\ObservableEventAbstract;
use RemotelyLiving\PHPDNS\Observability\Interfaces\Observable;
use RemotelyLiving\PHPDNS\Observability\Traits\Dispatcher;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DispatcherTest extends BaseTestAbstract
{
    private \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher;

    private \Symfony\Component\EventDispatcher\EventSubscriberInterface $subscriber;

    private \RemotelyLiving\PHPDNS\Observability\Events\ObservableEventAbstract $event;

    private \RemotelyLiving\PHPDNS\Observability\Interfaces\Observable $observableClass;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->subscriber = $this->createMock(EventSubscriberInterface::class);
        $this->event = new class extends ObservableEventAbstract
        {
            public static function getName(): string
            {
                return 'the name';
            }

            public function toArray(): array
            {
                return ['beep' => 'boop'];
            }
        };

        $this->observableClass = new class implements Observable {
            use Dispatcher;
        };
    }

    /**
     * @test
     */
    public function createsAndCachesDispatcher(): void
    {
        $this->dispatcher->expects($this->never())
            ->method('dispatch');

        $this->observableClass->dispatch($this->event);
    }

    /**
     * @test
     */
    public function addsSubscribersAndListeners(): void
    {
        $this->observableClass->setDispatcher($this->dispatcher);

        $this->dispatcher->expects($this->once())
            ->method('addSubscriber')
            ->with($this->subscriber);

        $this->dispatcher->expects($this->once())
            ->method('addListener')
            ->with('boop', function () {
            });

        $this->observableClass->addSubscriber($this->subscriber);
        $this->observableClass->addListener('boop', function () {
        });
    }

    /**
     * @test
     */
    public function dispatchesObservableEvents(): void
    {
        $this->observableClass->setDispatcher($this->dispatcher);

        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->event, $this->event::getName());

        $this->observableClass->dispatch($this->event);
    }
}
