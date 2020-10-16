<?php

namespace RemotelyLiving\PHPDNS\Observability\Performance\Interfaces;

use DateTimeInterface;

interface Time
{
    public function getMicrotime(): float;

    public function now(): DateTimeInterface;
}
