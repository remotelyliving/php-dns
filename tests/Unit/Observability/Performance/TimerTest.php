<?php

namespace RemotelyLiving\PHPDNS\Tests\Unit\Observability\Performance;

use DateTimeInterface;
use RemotelyLiving\PHPDNS\Observability\Performance\Timer;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

class TimerTest extends BaseTestAbstract
{
    private \RemotelyLiving\PHPDNS\Observability\Performance\Timer $timer;


    protected function setUp(): void
    {
        parent::setUp();

        $this->timer = new Timer();
    }

    /**
     * @test
     */
    public function getsNowAndMicroTime(): void
    {
        $this->assertGreaterThan(0, $this->timer->getMicroTime());
        $this->assertTrue(is_float($this->timer->getMicroTime()));

        $this->assertInstanceOf(DateTimeInterface::class, $this->timer->now());
    }
}
