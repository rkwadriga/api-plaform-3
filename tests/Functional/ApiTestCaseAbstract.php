<?php declare(strict_types=1);
/**
 * Created 2023-10-22 08:01:00
 * Author rkwadriga
 */

namespace App\Tests\Functional;

use App\Entity\User;
use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\HttpOptions;
use Zenstruck\Browser\KernelBrowser;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Proxy;

abstract class ApiTestCaseAbstract extends KernelTestCase
{
    use HasBrowser {
        browser as baseKernelBrowser;
    }

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        UserFactory::createMany(3);
        /** @var Proxy $user */
        $user = UserFactory::random();
        $this->user = $user->object();
    }

    protected function browser(array $options = [], array $server = []): KernelBrowser
    {
        return $this->baseKernelBrowser($options, $server)
            ->setDefaultHttpOptions(
                HttpOptions::create()->withHeader('Accept', 'application/ld+json')
            );
    }
}