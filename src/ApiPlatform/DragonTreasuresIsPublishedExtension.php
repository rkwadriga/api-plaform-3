<?php declare(strict_types=1);
/**
 * Created 2023-11-01 18:30:51
 * Author rkwadriga
 */

namespace App\ApiPlatform;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\DragonTreasure;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

class DragonTreasuresIsPublishedExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(
        private readonly Security $security
    ) {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        $this->addIsPublishedFilter($resourceClass, $queryBuilder);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, Operation $operation = null, array $context = []): void
    {
        $this->addIsPublishedFilter($resourceClass, $queryBuilder);
    }

    public function addIsPublishedFilter(string $resourceClass, QueryBuilder $queryBuilder): void
    {
        if ($resourceClass !== DragonTreasure::class) {
            return;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        $user = $this->security->getUser();
        if ($user !== null) {
            $queryBuilder->andWhere(sprintf('%s.isPublished = :isPublished OR %s.owner = :owner', $rootAlias, $rootAlias))
                ->setParameter('owner', $user);
        } else {
            $queryBuilder->andWhere(sprintf('%s.isPublished = :isPublished', $rootAlias));
        }

        $queryBuilder->setParameter('isPublished', true);
    }

}