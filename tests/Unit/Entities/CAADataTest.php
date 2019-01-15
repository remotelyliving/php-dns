<?php
namespace RemotelyLiving\PHPDNS\Tests\Unit\Entities;

use RemotelyLiving\PHPDNS\Entities\CAAData;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

class CAADataTest extends BaseTestAbstract
{

    private $flags = 0;

    private $tag = 'issue';

    private $value = '";"';

    /**
     * @var \RemotelyLiving\PHPDNS\Entities\CAAData
     */
    private $CAAData;

    protected function setUp()
    {
        parent::setUp();

        $this->CAAData = new CAAData($this->flags, $this->tag, $this->value);
    }

    /**
     * @test
     */
    public function knowsIfEquals()
    {
        $this->assertTrue($this->CAAData->equals($this->CAAData));
        $this->assertFalse($this->CAAData->equals(new CAAData(0, 'issue', 'boop.com')));
    }

    /**
     * @test
     */
    public function isArrayable()
    {
        $this->assertArrayableAndEquals(
            ['flags' => $this->flags, 'tag' => $this->tag, 'value' => ';'],
            $this->CAAData
        );
    }

    /**
     * @test
     */
    public function isSerializable()
    {
        $this->assertSerializable($this->CAAData);
        $this->assertEquals($this->CAAData, \unserialize(\serialize($this->CAAData)));
    }

    /**
     * @test
     */
    public function isStringable()
    {
        $this->assertStringableAndEquals('0 issue ";"', $this->CAAData);
    }

    /**
     * @test
     */
    public function hasBasicGetters()
    {
        $this->assertSame($this->flags, $this->CAAData->getFlags());
        $this->assertSame($this->tag, $this->CAAData->getTag());
        $this->assertSame(';', $this->CAAData->getValue());

        $nullDefault = new CAAData(0, 'issue');
        $this->assertNull($nullDefault->getValue());
    }
}
