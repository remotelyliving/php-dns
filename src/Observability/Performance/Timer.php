<?php

namespace RemotelyLiving\PHPDNS\Observability\Performance;

use DateTimeImmutable;
use DateTimeInterface;
use RemotelyLiving\PHPDNS\Observability\Performance\Interfaces\Time;

use function microtime;

final class Timer implements Time
{
    public function getMicroTime(): float
    {
        return microtime(true);
    }

    public function now(): DateTimeInterface
    {
        return new DateTimeImmutable('now');
    }
}
