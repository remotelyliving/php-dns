<?php
namespace RemotelyLiving\PHPDNS\Exceptions;

class Exception extends \Exception implements \JsonSerializable
{
    public function jsonSerialize(): array
    {
        return [
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'trace' => $this->getTraceAsString(),
        ];
    }
}
