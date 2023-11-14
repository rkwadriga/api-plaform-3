<?php declare(strict_types=1);
/**
 * Created 2023-11-07 16:57:49
 * Author rkwadriga
 */

namespace App\Mapper;

use App\ApiResource\DragonTreasureApi;
use App\ApiResource\UserApi;
use App\Entity\DragonTreasure;
use App\Entity\User;
use App\Repository\UserRepository;
use Exception;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: UserApi::class, to: User::class)]
class UserApiToEntityMapper implements MapperInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly PropertyAccessorInterface $propertyAccessor,
        private readonly MicroMapperInterface $microMapper
    ) {
    }

    /**
     * @param UserApi $from
     * @param string $toClass
     * @param array $context
     * @return User
     */
    public function load(object $from, string $toClass, array $context): object
    {
        return $this->getEntity($from);
    }

    /**
     * @param UserApi $from
     * @param User $to
     * @param array $context
     * @return User
     */
    public function populate(object $from, object $to, array $context): object
    {
        $to
            ->setEmail($from->email)
            ->setUsername($from->username)
        ;

        if ($from->password !== null) {
            $to->setPassword($this->passwordHasher->hashPassword($to, $from->password));
        }

        $dragonTreasures = array_map(fn (DragonTreasureApi $dto) => (
            $this->microMapper->map($dto, DragonTreasure::class, [MicroMapperInterface::MAX_DEPTH => 0])
        ), $from->dragonTreasures);

        $this->propertyAccessor->setValue($to, 'dragonTreasures', $dragonTreasures);

        return $to;
    }

    private function getEntity(UserApi $from): User
    {
        $entity = $from->id !== null ? $this->userRepository->find($from->id) : new User();
        if ($entity === null) {
            throw new Exception('User not found');
        }

        return $entity;
    }
}