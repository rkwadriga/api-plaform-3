<?php declare(strict_types=1);
/**
 * Created 2023-10-20 00:06:53
 * Author rkwadriga
 */

namespace App\ApiPlatform;

use ApiPlatform\OpenApi\Model\SecurityScheme;
use ArrayObject;
use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\OpenApi;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator('api_platform.openapi.factory')]
class OpenApiFactoryDecorator implements OpenApiFactoryInterface
{
    public function __construct(
        private readonly OpenApiFactoryInterface $decorated
    ) {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);

        $securitySchemes = $openApi->getComponents()->getSecuritySchemes() ?: new ArrayObject();
        $securitySchemes['access_token'] = new SecurityScheme(
            type: 'http',
            name: 'Authorization',
            scheme: 'bearer'
        );

        return $openApi;
    }
}