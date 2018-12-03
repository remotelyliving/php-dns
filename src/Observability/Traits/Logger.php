<?php
namespace RemotelyLiving\PHPDNS\Observability\Traits;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

trait Logger
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger = null;

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
