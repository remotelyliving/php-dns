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

    protected function setUp() : void
    {
        parent::setUp();

        $this->CAAData = new CAAData($this->flags, $this->tag, $this->value);
    }

    /**
     * @test
     */
    public function knowsIfEquals() : void
    {
        $this->assertTrue($this->CAAData->equals($this->CAAData));
        $this->assertFalse($this->CAAData->equals(new CAAData(0, 'issue', 'boop.com')));
    }

    /**
     * @test
     */
    public function isArrayable() : void
    {
        $this->assertArrayableAndEquals(
            ['flags' => $this->flags, 'tag' => $this->tag, 'value' => ';'],
            $this->CAAData
        );
    }

    /**
     * @test
     */
    public function isJsonSerializable() : void
    {
        $this->assertJsonSerializeableAndEquals(
            ['flags' => $this->flags, 'tag' => $this->tag, 'value' => ';'],
            $this->CAAData
        );
    }

    /**
     * @test
     */
    public function isSerializable() : void
    {
        $this->assertSerializable($this->CAAData);
        $this->assertEquals($this->CAAData, \unserialize(\serialize($this->CAAData)));
    }

    /**
     * @test
     */
    public function isStringable() : void
    {
        $this->assertStringableAndEquals('0 issue ";"', $this->CAAData);
    }

    /**
     * @test
     */
    public function hasBasicGetters() : void
    {
        $this->assertSame($this->flags, $this->CAAData->getFlags());
        $this->assertSame($this->tag, $this->CAAData->getTag());
        $this->assertSame(';', $this->CAAData->getValue());

        $nullDefault = new CAAData(0, 'issue');
        $this->assertNull($nullDefault->getValue());
    }
}
