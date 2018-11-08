<?php
namespace RemotelyLiving\PHPDNS\Observability\Performance\Interfaces;

interface Time
{
    public function getMicrotime(): float;

    public function now(): \DateTimeInterface;
}
