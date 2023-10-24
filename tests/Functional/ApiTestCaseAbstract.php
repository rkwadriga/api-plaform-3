<?php declare(strict_types=1);
/**
 * Created 2023-10-22 08:01:00
 * Author rkwadriga
 */

namespace App\Tests\Functional;

use App\Entity\User;
use App\Factory\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
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

    private ?User $_user = null;

    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
    }

    protected function browser(array $options = [], array $server = []): KernelBrowser
    {
        return $this->baseKernelBrowser($options, $server)
            ->setDefaultHttpOptions(
                HttpOptions::create()->withHeader('Accept', 'application/ld+json')
            );
    }

    protected function getUser(): User
    {
        if ($this->_user === null) {
            /** @var Proxy $user */
            $user = UserFactory::createOne();
            $this->_user = $user->object();
        }

        return $this->_user;
    }
}