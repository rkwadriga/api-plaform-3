<?php

namespace App\State;

use ApiPlatform\Doctrine\Odm\Paginator;
use ApiPlatform\Doctrine\Orm\State as OrmState;
use ApiPlatform\Metadata as Metadata;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\UserApi;
use App\Entity\User;
use ArrayIterator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class EntityToDtoStateProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: OrmState\CollectionProvider::class)]
        private readonly ProviderInterface $collectionProvider,
        #[Autowire(service: OrmState\ItemProvider::class)]
        private readonly ProviderInterface $itemProvider
    ) {
    }

    public function provide(Metadata\Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof Metadata\CollectionOperationInterface) {
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

        $entity = $this->itemProvider->provide($operation, $uriVariables, $context);

        return $entity ? $this->mapEntityToDto($entity) : null;
    }

    private function mapEntityToDto(User $user): UserApi
    {
        $dto = new UserApi();
        $dto->id = $user->getId();
        $dto->email = $user->getEmail();
        $dto->username = $user->getUsername();
        $dto->dragonTreasures = $user->getPublishedDragonTreasures()->toArray();
        $dto->flameThrowingDistance = rand(1, 10);

        return $dto;
    }
}
