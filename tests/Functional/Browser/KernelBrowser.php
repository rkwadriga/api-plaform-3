<?php declare(strict_types=1);
/**
 * Created 2023-10-25 05:16:53
 * Author rkwadriga
 */

namespace App\Tests\Functional\Browser;

use App\Entity\User;
use App\Factory\ApiTokenFactory;
use Zenstruck\Browser\KernelBrowser as BaseKernelBrowser;
use Zenstruck\Foundry\Proxy;

class KernelBrowser extends BaseKernelBrowser
{
    private array $headers = [];

    private array $tokens = [];

    public function asUser(User|Proxy $user, array $scopes = null): self
    {
        if (!isset($this->tokens[$user->getId()])) {
            $tokenOptions = [
                'ownedBy' => $user,
            ];
            if ($scopes !== null) {
                $tokenOptions['scopes'] = $scopes;
            }

            /** @var Proxy $token */
            $token = ApiTokenFactory::createOne($tokenOptions);

            $this->tokens[$user->getId()] = $token->object();
        }

        $this->headers['Authorization'] = 'Bearer ' . $this->tokens[$user->getId()]->getToken();

        return $this;
    }

    public function get(string $url, $options = []): BaseKernelBrowser
    {
        $this->prepareOptions($options);

        return parent::get($url, $options);
    }

    public function post(string $url, $options = []): BaseKernelBrowser
    {
        $this->prepareOptions($options);

        return parent::post($url, $options);
    }

    public function put(string $url, $options = []): BaseKernelBrowser
    {
        $this->prepareOptions($options);

        return parent::put($url, $options);
    }

    public function patch(string $url, $options = []): BaseKernelBrowser
    {
        $this->prepareOptions($options);

        return BaseKernelBrowser::patch($url, $options);
    }

    public function delete(string $url, $options = []): BaseKernelBrowser
    {
        $this->prepareOptions($options);

        return parent::delete($url, $options);
    }

    private function prepareOptions(array &$options): void
    {
        if (!isset($options['json'])) {
            $json = $options;
            $options = ['json' => $json];
        }

        $headers = $options['headers'] ?? [];
        if (!empty($this->headers)) {
            $headers += $this->headers;
        }

        if ($headers !== []) {
            $options['headers'] = $headers;
        }
    }
}