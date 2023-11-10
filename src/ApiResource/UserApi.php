<?php declare(strict_types=1);
/**
 * Created 2023-11-02 16:51:52
 * Author rkwadriga
 */

namespace App\ApiResource;

use ApiPlatform\Doctrine\Orm\State\Options;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata as Metadata;
use App\Entity\User;
use App\State\EntityClassDtoStateProcessor;
use App\State\EntityToDtoStateProvider;
use Symfony\Component\Validator\Constraints as Constraints;

#[Metadata\ApiResource(
    shortName: 'User',
    //normalizationContext: [Symfony\Component\Serializer\Normalizer\AbstractNormalizer::IGNORED_ATTRIBUTES => ['flameThrowingDistance']], // These properties will be not readable
    operations: [
        new Metadata\Get(),
        new Metadata\GetCollection(),
        new Metadata\Post(
            security: 'is_granted("PUBLIC_ACCESS")',
            validationContext: ['groups' => ['Default', 'postValidation']]
        ),
        new Metadata\Patch(
            security: 'is_granted("ROLE_USER_EDIT")',
        ),
        new Metadata\Delete()
    ],
    paginationItemsPerPage: 5,
    security: 'is_granted("ROLE_USER")',
    provider: EntityToDtoStateProvider::class,
    processor: EntityClassDtoStateProcessor::class,
    stateOptions: new Options(entityClass: User::class)
)]
#[Metadata\ApiFilter(SearchFilter::class, properties: [
    'username' => 'partial',
])]
class UserApi
{
    #[Metadata\ApiProperty(readable: false, writable: false, identifier: true)]
    public ?int $id = null;

    #[Constraints\NotBlank]
    #[Constraints\Email]
    public ?string $email = null;

    #[Constraints\NotBlank]
    public ?string $username = null;

    #[Metadata\ApiProperty(readable: false)]
    #[Constraints\NotBlank(groups: ['postValidation'])]
    public ?string $password = null;

    /**
     * @var array<DragonTreasureApi>
     */
    #[Metadata\ApiProperty(writable: false)]
    public array $dragonTreasures = [];

    #[Metadata\ApiProperty(writable: false)]
    public int $flameThrowingDistance = 0;
}