<?php declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter as Filter;
use ApiPlatform\Metadata as ApiMetadata;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use App\Repository\DragonTreasureRepository;
use Carbon\Carbon;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation as Annotation;
use Symfony\Component\Validator\Constraints as Assert;
use function Symfony\Component\String\u;

#[ORM\Entity(repositoryClass: DragonTreasureRepository::class)]
#[ApiMetadata\ApiResource(
    shortName: 'Treasure',
    description: 'A rare and valuable treasure.',
    operations: [
        new ApiMetadata\Get(normalizationContext: [
            'groups' => ['treasure:read', 'treasure:item:get'],
        ]),
        new ApiMetadata\GetCollection(),
        new ApiMetadata\Post(),
        new ApiMetadata\Put(),
        new ApiMetadata\Patch()
    ],
    formats: [
        'jsonld',
        'json',
        'html',
        'jsonhal',
        'csv' => 'text/csv',
    ],
    normalizationContext: [
        'groups' => ['treasure:read'],
    ],
    denormalizationContext: [
        'groups' => ['treasure:write'],
    ],
    paginationItemsPerPage: 10
)]
#[ApiMetadata\ApiResource(
    uriTemplate: '/users/{user_id}/treasures.{_format}',
    shortName: 'Treasure',
    operations: [new ApiMetadata\GetCollection()],
    uriVariables: [
        'user_id' => new ApiMetadata\Link(
            toProperty: 'owner',
            fromClass: User::class
        )
    ]
)]
#[ApiMetadata\ApiFilter(PropertyFilter::class)]
#[ApiMetadata\ApiFilter(Filter\SearchFilter::class, properties: ['owner.username' => 'partial'])]
class DragonTreasure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Annotation\Groups(['treasure:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'dragonTreasures')]
    #[ORM\JoinColumn(nullable: false)]
    #[Annotation\Groups(['treasure:read', 'treasure:write'])]
    #[Assert\Valid]
    private ?User $owner = null;

    #[ORM\Column(length: 255)]
    #[Annotation\Groups(['treasure:read', 'treasure:write', 'user:read', 'user:write'])]
    #[ApiMetadata\ApiFilter(Filter\SearchFilter::class, strategy: 'partial')]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50, maxMessage: 'Describe your loot in 50 chars or less')]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Annotation\Groups(['treasure:read'])]
    #[ApiMetadata\ApiFilter(Filter\SearchFilter::class, strategy: 'partial')]
    #[Assert\NotBlank]
    private ?string $description = null;

    #[ORM\Column]
    #[Annotation\Groups(['treasure:read', 'treasure:write', 'user:read', 'user:write'])]
    #[ApiMetadata\ApiFilter(Filter\RangeFilter::class)]
    #[Assert\GreaterThanOrEqual(0)]
    private int $value = 0;

    #[ORM\Column]
    #[Annotation\Groups(['treasure:read', 'treasure:write', 'user:write'])]
    #[Assert\GreaterThanOrEqual(0)]
    #[Assert\LessThanOrEqual(10)]
    private int $coolFactor = 0;

    #[ORM\Column]
    private DateTimeImmutable $plunderedAt;

    #[ORM\Column]
    #[ApiMetadata\ApiFilter(Filter\BooleanFilter::class)]
    private bool $isPublished = true;

    public function __construct()
    {
        $this->plunderedAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    #[Annotation\Groups(['treasure:write', 'user:write'])]
    #[Annotation\SerializedName('description')]
    public function setTextDescription(string $description): static
    {
        $this->description = nl2br($description);

        return $this;
    }

    #[Annotation\Groups(['treasure:read'])]
    public function getShortDescription(): ?string
    {
        return u($this->description)->truncate(40, '...');
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getCoolFactor(): int
    {
        return $this->coolFactor;
    }

    public function setCoolFactor(int $coolFactor): static
    {
        $this->coolFactor = $coolFactor;

        return $this;
    }

    public function getPlunderedAt(): DateTimeImmutable
    {
        return $this->plunderedAt;
    }

    public function setPlunderedAt(DateTimeImmutable $plunderedAt): static
    {
        $this->plunderedAt = $plunderedAt;

        return $this;
    }

    /**
     * A human-readable representation of when this treasure was plundered.
     */
    #[Annotation\Groups(['treasure:read'])]
    public function getPlunderedAtAgo(): string
    {
        return Carbon::instance($this->plunderedAt)->diffForHumans();
    }

    public function getIsPublished(): bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;

        return $this;
    }
}
