<?php
namespace RemotelyLiving\PHPDNS\Observability\Events;

use RemotelyLiving\PHPDNS\Observability\Performance\Profile;

class DNSQueryProfiled extends ObservableEventAbstract
{
    public const NAME = 'dns.query.profiled';

    /**
     * @var \RemotelyLiving\PHPDNS\Observability\Performance\Profile
     */
    private $profile;

    public function __construct(Profile $profile)
    {
        $this->profile = $profile;
    }

    public function getProfile(): Profile
    {
        return $this->profile;
    }

    public static function getName(): string
    {
        return self::NAME;
    }

    public function toArray(): array
    {
        return [
            'elapsedSeconds' => $this->profile->getElapsedSeconds(),
            'transactionName' => $this->profile->getTransactionName(),
            'peakMemoryUsage' => $this->profile->getPeakMemoryUsage(),
        ];
    }
}
