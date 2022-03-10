<?php

namespace RemotelyLiving\PHPDNS\Tests\Unit\Observability\Events;

use JsonSerializable;
use RemotelyLiving\PHPDNS\Entities\Interfaces\Arrayable;
use RemotelyLiving\PHPDNS\Observability\Events\ObservableEventAbstract;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

class ObservableEventAbstractTest extends BaseTestAbstract
{
    private \RemotelyLiving\PHPDNS\Observability\Events\ObservableEventAbstract $event;

    protected function setUp(): void
    {
        parent::setUp();

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
    }

    /**
     * @test
     */
    public function getsName(): void
    {
        $this->assertSame('the name', $this->event::getName());
    }

    /**
     * @test
     */
    public function isArrayable(): void
    {
        $this->assertInstanceOf(Arrayable::class, $this->event);
        $this->assertEquals(['beep' => 'boop'], $this->event->toArray());
    }

    /**
     * @test
     */
    public function isJsonSerializable(): void
    {
        $this->assertInstanceOf(JsonSerializable::class, $this->event);
        $this->assertEquals(['the name' => ['beep' => 'boop']], $this->event->jsonSerialize());
    }
}
