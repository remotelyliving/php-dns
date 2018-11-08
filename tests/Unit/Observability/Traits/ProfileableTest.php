<?php
namespace RemotelyLiving\PHPDNS\Tests\Unit\Observability\Traits;

use RemotelyLiving\PHPDNS\Observability\Performance\Profile;
use RemotelyLiving\PHPDNS\Observability\Performance\ProfileFactory;
use RemotelyLiving\PHPDNS\Observability\Traits\Profileable;
use RemotelyLiving\PHPDNS\Tests\Unit\BaseTestAbstract;

class ProfileableTest extends BaseTestAbstract
{
    /**
     * @var \RemotelyLiving\PHPDNS\Observability\Performance\Profile
     */
    private $profile;

    /**
     * @var \RemotelyLiving\PHPDNS\Observability\Performance\ProfileFactory
     */
    private $profileFactory;

    private $profileableClass;

    protected function setUp()
    {
        parent::setUp();

        $this->profile = new Profile('transactionName');
        $this->profileFactory = $this->createMock(ProfileFactory::class);
        $this->profileFactory->method('create')
            ->with('transactionName')
            ->willReturn($this->profile);

        $this->profileableClass = new class {
            use Profileable;
        };
    }

    /**
     * @test
     */
    public function createsProfiles()
    {
        $profile = $this->profileableClass->createProfile('name');
        $this->assertInstanceOf(Profile::class, $profile);
        $this->assertSame('name', $profile->getTransactionName());
    }

    /**
     * @test
     */
    public function setsAProfileFactory()
    {
        $this->profileableClass->setProfileFactory($this->profileFactory);
        $profile = $this->profileableClass->createProfile('transactionName');

        $this->assertInstanceOf(Profile::class, $profile);
        $this->assertSame('transactionName', $profile->getTransactionName());
    }
}
