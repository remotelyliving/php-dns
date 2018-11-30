<?php
namespace RemotelyLiving\PHPDNS\Tests\Unit\Entities;

use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

class DNSRecordTypeTest extends BaseTestAbstract
{
    /**
     * @test
     */
    public function hasConvenientFactoryMethods()
    {
        foreach (DNSRecordType::VALID_TYPES as $type) {
            $function = DNSRecordType::class . "::create{$type}";
            $createdType = $function();
            $this->assertSame($type, (string)$createdType);
            $this->assertEquals($createdType, DNSRecordType::createFromInt($createdType->toInt()));
        }
    }

    /**
     * @test
     * @expectedException \RemotelyLiving\PHPDNS\Exceptions\InvalidArgumentException
     */
    public function onlyAllowsValidTypes()
    {
        DNSRecordType::createFromString('SDFSF');
    }

    /**
     * @test
     */
    public function comparesItself()
    {
        $aRecord = DNSRecordType::createFromString('A');
        $cnameRecord = DNSRecordType::createFromString('CNAME');
        $otherARecord = DNSRecordType::createFromString('A');

        $this->assertFalse($aRecord->equals($cnameRecord));
        $this->assertTrue($otherARecord->equals($aRecord));

        $this->assertTrue($aRecord->isA('A'));
    }
}
