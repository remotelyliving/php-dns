<?php

namespace RemotelyLiving\PHPDNS\Tests\Unit\Entities;

use RemotelyLiving\PHPDNS\Entities\DNSRecordType;
use RemotelyLiving\PHPDNS\Exceptions\InvalidArgumentException;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

class DNSRecordTypeTest extends BaseTestAbstract
{
    /**
     * @test
     */
    public function hasConvenientNamedFactoryMethods(): void
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
     */
    public function onlyAllowsValidTypesFromStrings(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DNSRecordType::createFromString('SDFSF');
    }

    /**
     * @test
     */
    public function onlyAllowsValidTypesFromIntCodes(): void
    {
        $this->expectException(InvalidArgumentException::class);

        DNSRecordType::createFromInt(-100);
    }

    /**
     * @test
     */
    public function comparesItself(): void
    {
        $aRecord = DNSRecordType::createFromString('A');
        $cnameRecord = DNSRecordType::createFromString('CNAME');
        $otherARecord = DNSRecordType::createFromString('A');

        $this->assertFalse($aRecord->equals($cnameRecord));
        $this->assertTrue($otherARecord->equals($aRecord));

        $this->assertTrue($aRecord->isA('A'));
    }
}
