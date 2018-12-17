<?php
namespace RemotelyLiving\PHPDNS\Tests\Unit\Entities;

use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

class HostnameTest extends BaseTestAbstract
{
    /**
     * @var \RemotelyLiving\PHPDNS\Entities\Hostname
     */
    private $hostname;

    protected function setUp()
    {
        parent::setUp();

        $this->hostname = new Hostname('facebook.com');
    }

    /**
     * @test
     */
    public function hasBasicGettersAndIsStringy()
    {
        $this->assertSame('facebook.com.', (string)$this->hostname);
        $this->assertSame('facebook.com.', $this->hostname->getHostName());
        $this->assertSame('facebook.com', $this->hostname->getHostnameWithoutTrailingDot());
    }

    /**
     * @test
     */
    public function testsForEquality()
    {
        $facebook1 = Hostname::createFromString('facebook.com');
        $facebook2 = Hostname::createFromString('facebook.com');
        $google = Hostname::createFromString('google.com');

        $this->assertTrue($facebook1->equals($facebook2));
        $this->assertFalse($facebook2->equals($google));
    }

    /**
     * @test
     * @expectedException \RemotelyLiving\PHPDNS\Exceptions\InvalidArgumentException
     */
    public function doesNotAllowInvalidHostNames()
    {
        Hostname::createFromString('thing_.dfkljfs');
    }

    /**
     * @test
     */
    public function handlesIDNOperations()
    {
        $utf8IDN = 'ańodelgatos.com.';
        $IDN = Hostname::createFromString($utf8IDN);

        $expectedAscii = 'xn--aodelgatos-w0b.com.';
        $this->assertTrue($IDN->isPunycoded());
        $this->assertSame($expectedAscii, $IDN->getHostName());
        $this->assertSame($utf8IDN, $IDN->toUTF8());
    }
}
