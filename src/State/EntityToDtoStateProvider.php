<?php

namespace App\State;

use ApiPlatform\Doctrine\Odm\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\UserApi;
use App\Entity\User;
use ArrayIterator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class EntityToDtoStateProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.collection_provider')]
        private readonly ProviderInterface $collectionProvider
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        /** @var iterable<User>|Paginator $entities */
        $entities = $this->collectionProvider->provide($operation, $uriVariables, $context);

        $dtos = [];
        foreach ($entities as $user) {
            $dtos[] = $this->mapEntityToDto($user);
        }

        return new TraversablePaginator(
            new ArrayIterator($dtos),
            $entities->getCurrentPage(),
            $entities->getItemsPerPage(),
            $entities->getTotalItems()
        );
    }

    private function mapEntityToDto(User $user): UserApi
    {
        $dto = new UserApi();
        $dto->id = $user->getId();
        $dto->email = $user->getEmail();
        $dto->username = $user->getUsername();
        $dto->dragonTreasures = $user->getDragonTreasures()->toArray();

        return $dto;
    }
}
