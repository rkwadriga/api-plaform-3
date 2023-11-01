<?php declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata as ApiMetadata;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use App\Repository\UserRepository;
use App\State\UserHashPasswordProcessor;
use App\Validator\TreasureAllowedOwnerChange;
use Doctrine\Common\Collections as Collections;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User as Security;
use Symfony\Component\Serializer\Annotation as Annotation;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ApiMetadata\ApiResource(
    operations: [
        new ApiMetadata\Get(),
        new ApiMetadata\GetCollection(),
        new ApiMetadata\Post(
            security: 'is_granted("PUBLIC_ACCESS")',
            validationContext: [
                'groups' => [
                    'Default',
                    'PostValidation',
                ],
            ]
        ),
        new ApiMetadata\Put(security: 'is_granted("ROLE_USER_EDIT")'),
        new ApiMetadata\Patch(security: 'is_granted("ROLE_USER_EDIT")'),
        new ApiMetadata\Delete()
    ],
    normalizationContext: [
        'groups' => ['user:read'],
    ],
    denormalizationContext: [
        'groups' => ['user:write'],
    ],
    security: 'is_granted("ROLE_USER")',
    extraProperties: [
        'standard_put' => true,
    ]
)]
#[ApiMetadata\ApiResource(
    uriTemplate: '/treasures/{treasure_id}/owner.{_format}',
    operations: [new ApiMetadata\Get()],
    uriVariables: [
        'treasure_id' => new ApiMetadata\Link(
            fromProperty: 'owner',
            fromClass: DragonTreasure::class
        )
    ],
    security: 'is_granted("ROLE_USER")',
    extraProperties: [
        'standard_put' => true,
    ]
)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
#[ApiMetadata\ApiFilter(PropertyFilter::class)]
class User implements Security\UserInterface, Security\PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Annotation\Groups(['user:read', 'user:write', 'treasure:item:get'])]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Annotation\Groups(['user:read', 'user:write', 'treasure:item:get', 'treasure:write'])]
    #[Assert\NotBlank]
    private ?string $username = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: DragonTreasure::class, cascade: ['persist'], orphanRemoval: true)]
    #[Annotation\Groups(['user:write'])]
    #[Annotation\SerializedName('treasures')]
    #[Assert\Valid]
    #[TreasureAllowedOwnerChange]
    private Collections\Collection $dragonTreasures;

    #[ORM\OneToMany(mappedBy: 'ownedBy', targetEntity: ApiToken::class)]
    private Collections\Collection $apiTokens;

    /** Scopes given during API authentication */
    private ?array $accessTokenScopes = null;

    #[Annotation\Groups(['user:write'])]
    #[Annotation\SerializedName('password')]
    #[Assert\NotBlank(groups: ['PostValidation'])]
    private ?string $plainPassword = null;

    public function __construct()
    {
        $this->dragonTreasures = new Collections\ArrayCollection();
        $this->apiTokens = new Collections\ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        if ($this->accessTokenScopes === null) {
            // Logged in as full, normal user
            $roles[] = 'ROLE_FULL_USER';
        } else {
            $roles += $this->accessTokenScopes;
        }

        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $painPassword): static
    {
        $this->plainPassword = $painPassword;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    /**
     * @return Collections\Collection<int, DragonTreasure>
     */
    public function getDragonTreasures(): Collections\Collection
    {
        return $this->dragonTreasures;
    }

    public function addDragonTreasure(DragonTreasure $dragonTreasure): static
    {
        if (!$this->dragonTreasures->contains($dragonTreasure)) {
            $this->dragonTreasures->add($dragonTreasure);
            $dragonTreasure->setOwner($this);
        }

        return $this;
    }

    public function removeDragonTreasure(DragonTreasure $dragonTreasure): static
    {
        if ($this->dragonTreasures->removeElement($dragonTreasure)) {
            // set the owning side to null (unless already changed)
            if ($dragonTreasure->getOwner() === $this) {
                $dragonTreasure->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collections\Collection<int, DragonTreasure>
     */
    #[Annotation\Groups(['user:read'])]
    #[Annotation\SerializedName('treasures')]
    public function getPublishedDragonTreasures(): Collections\Collection
    {
        return $this->dragonTreasures->filter(fn (DragonTreasure $treasure) => $treasure->getIsPublished());
    }

    /**
     * @return Collections\Collection<int, ApiToken>
     */
    public function getApiTokens(): Collections\Collection
    {
        return $this->apiTokens;
    }

    public function addApiToken(ApiToken $apiToken): static
    {
        if (!$this->apiTokens->contains($apiToken)) {
            $this->apiTokens->add($apiToken);
            $apiToken->setOwnedBy($this);
        }

        return $this;
    }

    public function removeApiToken(ApiToken $apiToken): static
    {
        if ($this->apiTokens->removeElement($apiToken)) {
            // set the owning side to null (unless already changed)
            if ($apiToken->getOwnedBy() === $this) {
                $apiToken->setOwnedBy(null);
            }
        }

        return $this;
    }

    public function getValidTokenStrings(): array
    {
        return $this->getApiTokens()
            ->filter(fn (ApiToken $token) => $token->isValid())
            ->map(fn (ApiToken $token) => $token->getToken())
            ->toArray()
        ;
    }

    public function markAsTokenAuthenticated(array $scopes): void
    {
        $this->accessTokenScopes = $scopes;
    }
}
