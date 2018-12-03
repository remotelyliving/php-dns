<?php
namespace RemotelyLiving\PHPDNS\Resolvers\Interfaces;

use Psr\Log\LoggerAwareInterface;
use RemotelyLiving\PHPDNS\Observability\Interfaces\Observable;

interface ObservableResolver extends Resolver, Observable, LoggerAwareInterface
{

}
