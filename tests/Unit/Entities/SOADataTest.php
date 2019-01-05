<?php
namespace RemotelyLiving\PHPDNS\Tests\Unit\Entities;

use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Entities\SOAData;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

class SOADataTest extends BaseTestAbstract
{
    /**
     * @var \RemotelyLiving\PHPDNS\Entities\Hostname
     */
    private $mname;

    /**
     * @var \RemotelyLiving\PHPDNS\Entities\Hostname
     */
    private $rname;

    /**
     * @var int
     */
    private $serial = 2342;

    /**
     * @var int
     */
    private $refresh = 123;

    /**
     * @var int
     */
    private $retry = 321;

    /**
     * @var int
     */
    private $expire = 3434;

    /**
     * @var int
     */
    private $minTTL = 60;

    /**
     * @var \RemotelyLiving\PHPDNS\Entities\SOAData
     */
    private $SOAData;

    protected function setUp()
    {
        parent::setUp();

        $this->mname = new Hostname('google.com');
        $this->rname = new Hostname('facebook.com');
        $this->SOAData = new SOAData(
            $this->mname,
            $this->rname,
            $this->serial,
            $this->refresh,
            $this->retry,
            $this->expire,
            $this->minTTL
        );
    }

    /**
     * @test
     */
    public function knowsIfEquals()
    {
        $anotherSOA = new SOAData($this->rname, $this->mname, 1, 1, 1, 1, 1);
        $this->assertTrue($this->SOAData->equals($this->SOAData));
        $this->assertFalse($this->SOAData->equals($anotherSOA));
    }

    /**
     * @test
     */
    public function isArrayable()
    {
        $this->assertArrayableAndEquals(
            [
                'rname' => (string)$this->rname,
                'mname' => (string)$this->mname,
                'serial' => $this->serial,
                'refresh' => $this->refresh,
                'retry' => $this->retry,
                'expire' => $this->expire,
                'minimumTTL' => $this->minTTL,
            ],
            $this->SOAData
        );
    }

    /**
     * @test
     */
    public function isSerializable()
    {
        $this->assertSerializable($this->SOAData);
        $this->assertEquals($this->SOAData, \unserialize(\serialize($this->SOAData)));
    }

    /**
     * @test
     */
    public function isStringable()
    {
        $this->assertStringableAndEquals('google.com. facebook.com. 2342 123 321 3434 60', $this->SOAData);
    }

    /**
     * @test
     */
    public function hasBasicGetters()
    {
        $this->assertSame($this->mname, $this->SOAData->getMname());
        $this->assertSame($this->rname, $this->SOAData->getRname());
        $this->assertSame($this->serial, $this->SOAData->getSerial());
        $this->assertSame($this->refresh, $this->SOAData->getRefresh());
        $this->assertSame($this->retry, $this->SOAData->getRetry());
        $this->assertSame($this->expire, $this->SOAData->getExpire());
        $this->assertSame($this->minTTL, $this->SOAData->getMinTTL());
    }
}
