<?php

namespace RemotelyLiving\PHPDNS\Observability\Traits;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

trait Logger
{
    private ?\Psr\Log\LoggerInterface $logger = null;

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    protected function getLogger(): LoggerInterface
    {
        if ($this->logger === null) {
            $this->logger = new NullLogger();
        }

        return $this->logger;
    }
}
