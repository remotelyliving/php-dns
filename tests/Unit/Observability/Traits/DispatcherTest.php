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
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var \Symfony\Component\EventDispatcher\EventSubscriberInterface
     */
    private $subscriber;

    /**
     * @var \RemotelyLiving\PHPDNS\Observability\Events\ObservableEventAbstract
     */
    private $event;

    /**
     * @var \RemotelyLiving\PHPDNS\Observability\Interfaces\Observable
     */
    private $observableClass;

    protected function setUp()
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
    public function createsAndCachesDispatcher()
    {
        $this->dispatcher->expects($this->never())
            ->method('dispatch');

        $this->observableClass->dispatch($this->event);
    }

    /**
     * @test
     */
    public function addsSubscribersAndListeners()
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
    public function dispatchesObservableEvents()
    {
        $this->observableClass->setDispatcher($this->dispatcher);

        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with('the name', $this->event);

        $this->observableClass->dispatch($this->event);
    }
}
