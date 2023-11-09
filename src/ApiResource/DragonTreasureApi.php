<?php declare(strict_types=1);
/**
 * Created 2023-11-09 17:14:43
 * Author rkwadriga
 */

namespace App\ApiResource;

use ApiPlatform\Doctrine\Orm\State\Options;
use App\Entity\DragonTreasure;
use ApiPlatform\Metadata as Metadata;
use App\State\EntityClassDtoStateProcessor;
use App\State\EntityToDtoStateProvider;

#[Metadata\ApiResource(
    shortName: 'Treasure',
    //normalizationContext: [Symfony\Component\Serializer\Normalizer\AbstractNormalizer::IGNORED_ATTRIBUTES => ['flameThrowingDistance']], // These properties will be not readable
    paginationItemsPerPage: 10,
    //security: 'is_granted("ROLE_USER")',
    provider: EntityToDtoStateProvider::class,
    processor: EntityClassDtoStateProcessor::class,
    stateOptions: new Options(entityClass: DragonTreasure::class)
)]
class DragonTreasureApi
{
    public ?int $id = null;

    public ?string $name = null;

    public ?UserApi $owner = null;
}