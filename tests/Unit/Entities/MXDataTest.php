<?php
namespace RemotelyLiving\PHPDNS\Tests\Unit\Entities;

use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Entities\MXData;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

class MXDataTest extends BaseTestAbstract
{
    /**
     * @var \RemotelyLiving\PHPDNS\Entities\Hostname
     */
    private $target;

    /**
     * @var int
     */
    private $priority = 60;

    /**
     * @var \RemotelyLiving\PHPDNS\Entities\MXData
     */
    private $MXData;

    protected function setUp()
    {
        parent::setUp();

        $this->target = new Hostname('google.com');
        $this->MXData = new MXData($this->target, $this->priority);
    }

    /**
     * @test
     */
    public function knowsIfEquals()
    {
        $this->assertTrue($this->MXData->equals($this->MXData));
        $this->assertFalse($this->MXData->equals(new MXData(new Hostname('boop.com'), 60)));
    }

    /**
     * @test
     */
    public function isArrayable()
    {
        $this->assertArrayableAndEquals(
            ['target' => (string)$this->target, 'priority' => $this->priority],
            $this->MXData
        );
    }

    /**
     * @test
     */
    public function isSerializable()
    {
        $this->assertSerializable($this->MXData);
        $this->assertEquals($this->MXData, \unserialize(\serialize($this->MXData)));
    }

    /**
     * @test
     */
    public function isStringable()
    {
        $this->assertStringableAndEquals('60 google.com.', $this->MXData);
    }

    /**
     * @test
     */
    public function hasBasicGetters()
    {
        $this->assertSame($this->target, $this->MXData->getTarget());
        $this->assertSame($this->priority, $this->MXData->getPriority());
    }
}
