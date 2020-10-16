<?php

namespace RemotelyLiving\PHPDNS\Observability\Performance;

use RemotelyLiving\PHPDNS\Observability\Performance\Interfaces\ProfileInterface;
use RemotelyLiving\PHPDNS\Observability\Performance\Interfaces\Time;

final class Profile implements ProfileInterface
{
    private string $transactionName;

    private float $startTime = 0.0;

    private float $stopTime = 0.0;

    private int $peakMemoryUsage = 0;

    private \RemotelyLiving\PHPDNS\Observability\Performance\Interfaces\Time $time;

    public function __construct(string $transactionName, Time $time = null)
    {
        $this->transactionName = $transactionName;
        $this->time = $time ?? new Timer();
    }

    public function startTransaction(): void
    {
        $this->startTime = $this->time->getMicroTime();
    }

    public function endTransaction(): void
    {
        $this->stopTime = $this->time->getMicroTime();
    }

    public function getTransactionName(): string
    {
        return $this->transactionName;
    }

    public function getElapsedSeconds(): float
    {
        return $this->stopTime - $this->startTime;
    }

    public function samplePeakMemoryUsage(): void
    {
        $this->peakMemoryUsage = \memory_get_peak_usage();
    }

    public function getPeakMemoryUsage(): int
    {
        return $this->peakMemoryUsage;
    }
}
