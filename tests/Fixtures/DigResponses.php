<?php

declare(strict_types=1);

namespace RemotelyLiving\PHPDNS\Tests\Fixtures;

use RemotelyLiving\PHPDNS\Entities\Hostname;

class DigResponses
{
    public static function empty(): string
    {
        return '';
    }

    public static function ARecords(Hostname $hostname): string
    {
        return <<<EOT
{$hostname}     1798 IN A 198.185.159.145
{$hostname}     1798 IN A 198.185.159.144
{$hostname}     1798 IN A 198.49.23.145
{$hostname}     1798 IN A 198.49.23.144
EOT;
    }

    public static function anyRecords(Hostname $hostname): string
    {
        return <<<EOT
{$hostname}     1626 IN A 198.49.23.145
{$hostname}     1626 IN A 198.49.23.144
{$hostname}     1626 IN A 198.185.159.145
{$hostname}     1626 IN A 198.185.159.144
{$hostname}     1659 IN NS dns1.registrar-servers.com.
{$hostname}     1659 IN NS dns2.registrar-servers.com.
{$hostname}     3429 IN SOA dns1.registrar-servers.com. hostmaster.registrar-servers.com. (
                                1573438377 ; serial
                                43200      ; refresh (12 hours)
                                3600       ; retry (1 hour)
                                604800     ; expire (1 week)
                                3601       ; minimum (1 hour 1 second)
                                )
{$hostname}     1799 IN MX 10 aspmx2.googlemail.com.
{$hostname}     1799 IN MX 10 aspmx3.googlemail.com.
{$hostname}     1799 IN MX 5 alt1.aspmx.l.google.com.
{$hostname}     1799 IN MX 5 alt2.aspmx.l.google.com.
{$hostname}     1799 IN MX 1 aspmx.l.google.com.
{$hostname}     1798 IN TXT "keybase-site-verification=4qdfyf_6CLK7Un2-IsNRUsISjMlMnc5dxP2rMDmDgh0"
EOT;
    }
}
