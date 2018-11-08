<?php
namespace RemotelyLiving\PHPDNS\Resolvers\Interfaces;

interface Randomizable extends Resolver
{
    public function randomly(): Randomizable;
}
