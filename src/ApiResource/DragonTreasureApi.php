<?php declare(strict_types=1);
/**
 * Created 2023-11-09 17:14:43
 * Author rkwadriga
 */

namespace App\ApiResource;

use ApiPlatform\Doctrine\Orm\State\Options;
use App\Entity\DragonTreasure;
use ApiPlatform\Metadata as Metadata;
use App\State\DragonTreasureApiStateProcessor;
use App\State\EntityClassDtoStateProcessor;
use App\State\EntityToDtoStateProvider;
use App\Validator\IsValidOwner;
use Symfony\Component\Validator\Constraints as Assert;

#[Metadata\ApiResource(
    shortName: 'Treasure',
    operations: [
        new Metadata\Get(),
        new Metadata\GetCollection(),
        new Metadata\Post(
            security: 'is_granted("ROLE_TREASURE_CREATE")'
        ),
        new Metadata\Patch(
            security: 'is_granted("EDIT", object)'
        ),
        new Metadata\Delete(
            security: 'is_granted("ROLE_ADMIN")'
        )
    ],
    //normalizationContext: [Symfony\Component\Serializer\Normalizer\AbstractNormalizer::IGNORED_ATTRIBUTES => ['flameThrowingDistance']], // These properties will be not readable
    paginationItemsPerPage: 10,
    //security: 'is_granted("ROLE_USER")',
    provider: EntityToDtoStateProvider::class,
    processor: DragonTreasureApiStateProcessor::class,
    stateOptions: new Options(entityClass: DragonTreasure::class)
)]
class DragonTreasureApi
{
    public ?int $id = null;

    #[Assert\Valid]
    #[IsValidOwner]
    public ?UserApi $owner = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50, maxMessage: 'Describe your loot in 50 chars or less')]
    public ?string $name = null;

    #[Assert\NotBlank]
    public ?string $description = null;

    #[Assert\GreaterThanOrEqual(0)]
    public int $value = 0;

    #[Assert\GreaterThanOrEqual(0)]
    #[Assert\LessThanOrEqual(10)]
    public int $coolFactor = 0;

    public ?string $shortDescription = null;

    public ?string $plunderedAtAgo = null;

    #[Metadata\ApiProperty(security: 'object === null or is_granted("EDIT", object)')]
    public bool $isPublished = false;

    public ?bool $isMine = null;
}