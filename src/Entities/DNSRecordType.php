<?php
namespace RemotelyLiving\PHPDNS\Entities;

use RemotelyLiving\PHPDNS\Exceptions\InvalidArgumentException;

class DNSRecordType extends EntityAbstract
{
    public const TYPE_A = 'A';
    public const TYPE_CNAME = 'CNAME';
    public const TYPE_HINFO = 'HINFO';
    public const TYPE_CAA = 'CAA';
    public const TYPE_MX = 'MX';
    public const TYPE_NS = 'NS';
    public const TYPE_PTR = 'PTR';
    public const TYPE_SOA = 'SOA';
    public const TYPE_TXT = 'TXT';
    public const TYPE_AAAA = 'AAAA';
    public const TYPE_SRV = 'SRV';
    public const TYPE_NAPTR = 'NAPTR';
    public const TYPE_A6 = 'A6';
    public const TYPE_ANY = 'ANY';

    public const VALID_TYPES = [
        self::TYPE_A,
        self::TYPE_CNAME,
        self::TYPE_HINFO,
        self::TYPE_CAA,
        self::TYPE_MX,
        self::TYPE_NS,
        self::TYPE_PTR,
        self::TYPE_SOA,
        self::TYPE_TXT,
        self::TYPE_AAAA,
        self::TYPE_SRV,
        self::TYPE_NAPTR,
        self::TYPE_A6,
        self::TYPE_ANY,
    ];

    protected const CODE_TYPE_MAP = [
        1 => DNSRecordType::TYPE_A,
        5 => DNSRecordType::TYPE_CNAME,
        13 => DNSRecordType::TYPE_HINFO,
        257 => DNSRecordType::TYPE_CAA,
        15 => DNSRecordType::TYPE_MX,
        2 => DNSRecordType::TYPE_NS,
        12 => DNSRecordType::TYPE_PTR,
        6 => DNSRecordType::TYPE_SOA,
        16 => DNSRecordType::TYPE_TXT,
        28 => DNSRecordType::TYPE_AAAA,
        33 => DNSRecordType::TYPE_SRV,
        35 => DNSRecordType::TYPE_NAPTR,
        38 => DNSRecordType::TYPE_A6,
        255 => DNSRecordType::TYPE_ANY,
    ];

    /**
     * @var string
     */
    private $type;

    public function __construct(string $type)
    {
        if (!in_array($type, self::VALID_TYPES, true)) {
            throw new InvalidArgumentException("{$type} is not a valid DNS query type");
        }

        $this->type = $type;
    }

    public function __toString(): string
    {
        return $this->type;
    }

    public static function createFromInt(int $code) : DNSRecordType
    {
        return new static(self::CODE_TYPE_MAP[$code]);
    }

    public static function createFromString(string $type) : DNSRecordType
    {
        return new static($type);
    }

    public function toInt(): int
    {
        return (int) array_flip(self::CODE_TYPE_MAP)[$this->type];
    }

    public function isA(string $type): bool
    {
        return $this->type === strtoupper($type);
    }

    public function equals(DNSRecordType $recordType): bool
    {
        return $this->type === (string)$recordType;
    }

    public static function createA(): self
    {
        return self::createFromString(self::TYPE_A);
    }

    public static function createCNAME(): self
    {
        return self::createFromString(self::TYPE_CNAME);
    }

    public static function createHINFO(): self
    {
        return self::createFromString(self::TYPE_HINFO);
    }

    public static function createCAA(): self
    {
        return self::createFromString(self::TYPE_CAA);
    }

    public static function createMX(): self
    {
        return self::createFromString(self::TYPE_MX);
    }

    public static function createNS(): self
    {
        return self::createFromString(self::TYPE_NS);
    }

    public static function createPTR(): self
    {
        return self::createFromString(self::TYPE_PTR);
    }

    public static function createSOA(): self
    {
        return self::createFromString(self::TYPE_SOA);
    }

    public static function createTXT(): self
    {
        return self::createFromString(self::TYPE_TXT);
    }

    public static function createAAAA(): self
    {
        return self::createFromString(self::TYPE_AAAA);
    }

    public static function createSRV(): self
    {
        return self::createFromString(self::TYPE_SRV);
    }

    public static function createNAPTR(): self
    {
        return self::createFromString(self::TYPE_NAPTR);
    }

    public static function createA6(): self
    {
        return self::createFromString(self::TYPE_A6);
    }

    public static function createANY(): self
    {
        return self::createFromString(self::TYPE_ANY);
    }
}
