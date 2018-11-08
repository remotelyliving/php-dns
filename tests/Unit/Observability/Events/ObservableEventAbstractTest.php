<?php
namespace RemotelyLiving\PHPDNS\Tests\Unit\Observability\Events;

use RemotelyLiving\PHPDNS\Entities\Interfaces\Arrayable;
use RemotelyLiving\PHPDNS\Observability\Events\ObservableEventAbstract;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

class ObservableEventAbstractTest extends BaseTestAbstract
{
    /**
     * @var \RemotelyLiving\PHPDNS\Observability\Events\ObservableEventAbstract
     */
    private $event;

    protected function setUp()
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
    public function getsName()
    {
        $this->assertSame('the name', $this->event::getName());
    }

    /**
     * @test
     */
    public function isArrayable()
    {
        $this->assertInstanceOf(Arrayable::class, $this->event);
        $this->assertEquals(['beep' => 'boop'], $this->event->toArray());
    }

    /**
     * @test
     */
    public function isJsonSerializable()
    {
        $this->assertInstanceOf(\JsonSerializable::class, $this->event);
        $this->assertEquals(['the name' => ['beep' => 'boop']], $this->event->jsonSerialize());
    }
}
