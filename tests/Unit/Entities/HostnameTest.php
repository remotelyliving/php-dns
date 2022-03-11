<?php

namespace RemotelyLiving\PHPDNS\Tests\Unit\Entities;

use RemotelyLiving\PHPDNS\Entities\Hostname;
use RemotelyLiving\PHPDNS\Exceptions\InvalidArgumentException;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

class HostnameTest extends BaseTestAbstract
{
    private \RemotelyLiving\PHPDNS\Entities\Hostname $hostname;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hostname = new Hostname('facebook.com');
    }

    /**
     * @test
     */
    public function hasBasicGettersAndIsStringy(): void
    {
        $this->assertSame('facebook.com.', (string)$this->hostname);
        $this->assertSame('facebook.com.', $this->hostname->getHostName());
        $this->assertSame('facebook.com', $this->hostname->getHostnameWithoutTrailingDot());
    }

    /**
     * @test
     */
    public function testsForEquality(): void
    {
        $facebook1 = Hostname::createFromString('facebook.com');
        $facebook2 = Hostname::createFromString('facebook.com');
        $google = Hostname::createFromString('google.com');

        $this->assertTrue($facebook1->equals($facebook2));
        $this->assertFalse($facebook2->equals($google));
    }

    /**
     * @test
     */
    public function doesNotAllowInvalidHostNames(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $hostname = implode('', array_fill(0, 64, 'A'));

        Hostname::createFromString($hostname);
    }

    /**
     * @test
     */
    public function handlesIDNOperations(): void
    {
        $utf8IDN = 'ańodelgatos.com.';
        $IDN = Hostname::createFromString($utf8IDN);

        $expectedAscii = 'xn--aodelgatos-w0b.com.';
        $this->assertTrue($IDN->isPunycoded());
        $this->assertSame($expectedAscii, $IDN->getHostName());
        $this->assertSame($utf8IDN, $IDN->toUTF8());
    }

    /**
     * @test
     * @dataProvider validHostnamesProvider
     */
    public function createsHostnamesFromString(string $hostname): void
    {
        $this->assertInstanceOf(Hostname::class, Hostname::createFromString($hostname));
    }

    public function validHostnamesProvider(): array
    {
        return [
            ['google.com'],
            ['subdomain.google.com'],
            ['mandrill._domainkey.domain.com'],
            ['google.com.'],
        ];
    }
}
