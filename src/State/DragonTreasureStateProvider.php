<?php

namespace App\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\PartialPaginatorInterface;
use ApiPlatform\State\ProviderInterface;
use App\Entity\DragonTreasure;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class DragonTreasureStateProvider implements ProviderInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private readonly ProviderInterface $itemProvider,
        #[Autowire(service: 'api_platform.doctrine.orm.state.collection_provider')]
        private readonly ProviderInterface $collectionProvider,
        private readonly Security $security
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            /** @var iterable<DragonTreasure> $paginator */
            $paginator = $this->collectionProvider->provide($operation, $uriVariables, $context);
            foreach ($paginator as $treasure) {
                $this->provideItem($treasure);
            }

            return $paginator;
        }

        $treasure = $this->itemProvider->provide($operation, $uriVariables, $context);

        return $this->provideItem($treasure);
    }

    public function provideItem(PartialPaginatorInterface|array|DragonTreasure|null $treasure): PartialPaginatorInterface|array|DragonTreasure|null
    {
        if (!is_a($treasure, DragonTreasure::class)) {
            return $treasure;
        }

        $user = $this->security->getUser();

        return $treasure->setIsOwnedByAuthenticatedUser($user !== null && $user === $treasure->getOwner());
    }
}
