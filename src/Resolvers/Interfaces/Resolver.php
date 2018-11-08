<?php
namespace RemotelyLiving\PHPDNS\Resolvers\Interfaces;

interface Resolver extends DNSQuery
{
    public function getName(): string;
}
