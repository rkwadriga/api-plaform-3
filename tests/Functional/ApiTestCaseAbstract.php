<?php declare(strict_types=1);
/**
 * Created 2023-10-22 08:01:00
 * Author rkwadriga
 */

namespace App\Tests\Functional;

use App\Tests\Functional\Browser\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\HttpOptions;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Browser\KernelBrowser as BaseKernelBrowser;

abstract class ApiTestCaseAbstract extends KernelTestCase
{
    use HasBrowser {
        browser as baseKernelBrowser;
    }

    /**
     * @param array $options
     * @param array $server
     * @return KernelBrowser
     */
    protected function browser(array $options = [], array $server = []): BaseKernelBrowser
    {
        // Set re-initialized KernelBrowser class
        $_SERVER['KERNEL_BROWSER_CLASS'] = KernelBrowser::class;

        return $this->baseKernelBrowser($options, $server)
            ->setDefaultHttpOptions(
                HttpOptions::create()->withHeader('Accept', 'application/ld+json')
            );
    }
}