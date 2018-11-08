<?php
namespace RemotelyLiving\PHPDNS\Tests\Unit\Observability\Performance;

use RemotelyLiving\PHPDNS\Observability\Performance\Timer;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

class TimerTest extends BaseTestAbstract
{
    /**
     * @var \RemotelyLiving\PHPDNS\Observability\Performance\Timer
     */
    private $timer;


    protected function setUp()
    {
        parent::setUp();

        $this->timer = new Timer();
    }

    /**
     * @test
     */
    public function getsNowAndMicroTime()
    {
        $this->assertGreaterThan(0, $this->timer->getMicroTime());
        $this->assertTrue(is_float($this->timer->getMicroTime()));

        $this->assertInstanceOf(\DateTimeInterface::class, $this->timer->now());
    }
}
