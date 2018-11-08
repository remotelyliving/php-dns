<?php
namespace RemotelyLiving\PHPDNS\Observability\Performance;

use RemotelyLiving\PHPDNS\Observability\Performance\Interfaces\Time;

class Profile
{
    /**
     * @var string
     */
    private $transactionName;

    /**
     * @var float
     */
    private $startTime = 0.0;

    /**
     * @var float
     */
    private $stopTime = 0.0;

    /**
     * @var int
     */
    private $peakMemoryUsage = 0;

    /**
     * @var \RemotelyLiving\PHPDNS\Observability\Performance\Interfaces\Time
     */
    private $time;

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
        $this->peakMemoryUsage = memory_get_peak_usage();
    }

    public function getPeakMemoryUsage(): int
    {
        return $this->peakMemoryUsage;
    }
}
