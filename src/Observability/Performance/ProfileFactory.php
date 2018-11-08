<?php
namespace RemotelyLiving\PHPDNS\Observability\Performance;

class ProfileFactory
{
    public function create(string $transactionName): Profile
    {
        return new Profile($transactionName);
    }
}
