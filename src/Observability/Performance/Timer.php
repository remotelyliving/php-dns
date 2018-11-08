<?php
namespace RemotelyLiving\PHPDNS\Observability\Performance;

use RemotelyLiving\PHPDNS\Observability\Performance\Interfaces\Time;

class Timer implements Time
{
    public function getMicroTime(): float
    {
        return microtime(true);
    }

    public function now(): \DateTimeInterface
    {
        return new \DateTimeImmutable('now');
    }
}
