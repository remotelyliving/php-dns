<?php
namespace RemotelyLiving\PHPDNS\Tests\Unit\Observability\Performance;

use RemotelyLiving\PHPDNS\Observability\Performance\Interfaces\Time;
use RemotelyLiving\PHPDNS\Observability\Performance\Profile;
use RemotelyLiving\PHPDNS\Observability\Performance\ProfileFactory;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

class ProfileTest extends BaseTestAbstract
{
    /**
     * @var \RemotelyLiving\PHPDNS\Observability\Performance\Interfaces\Time
     */
    private $time;

    /**
     * @var \RemotelyLiving\PHPDNS\Observability\Performance\ProfileFactory
     */
    private $factory;

    /**
     * @var \RemotelyLiving\PHPDNS\Observability\Performance\Profile
     */
    private $profile;

    protected function setUp()
    {
        parent::setUp();

        $this->time = $this->createMock(Time::class);
        $this->time->method('getMicroTime')
            ->willReturnOnConsecutiveCalls(1.0, 5.0);

        $this->factory = new ProfileFactory();
        $this->profile = new Profile('transactionName', $this->time);
    }

    /**
     * @test
     */
    public function timesTransactions()
    {
        $this->profile->startTransaction();
        $this->profile->endTransaction();

        $this->assertSame(4.0, $this->profile->getElapsedSeconds());
    }

    /**
     * @test
     */
    public function getsPeakMemoryUsage()
    {
        $this->profile->samplePeakMemoryUsage();
        $this->assertSame($this->profile->getPeakMemoryUsage(), memory_get_peak_usage());
    }

    /**
     * @test
     */
    public function getsATransactionName()
    {
        $this->assertSame('transactionName', $this->profile->getTransactionName());
    }

    /**
     * @test
     */
    public function hasAnAccompanyingFactory()
    {
        $this->assertEquals(new Profile('transactionName'), $this->factory->create('transactionName'));
    }
}
