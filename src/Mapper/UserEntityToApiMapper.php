<?php declare(strict_types=1);
/**
 * Created 2023-11-07 17:19:24
 * Author rkwadriga
 */

namespace App\Mapper;

use App\ApiResource\DragonTreasureApi;
use App\ApiResource\UserApi;
use App\Entity\DragonTreasure;
use App\Entity\User;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: User::class, to: UserApi::class)]
class UserEntityToApiMapper implements MapperInterface
{
    public function __construct(
        private readonly MicroMapperInterface $microMapper
    ) {
    }

    /**
     * @param User $from
     * @param string $toClass
     * @param array $context
     * @return UserApi
     */
    public function load(object $from, string $toClass, array $context): object
    {
        $dto = new UserApi();
        $dto->id = $from->getId();

        return $dto;
    }

    /**
     * @param User $from
     * @param UserApi $to
     * @param array $context
     * @return UserApi
     */
    public function populate(object $from, object $to, array $context): object
    {
        $to->email = $from->getEmail();
        $to->username = $from->getUsername();
        $to->dragonTreasures = array_map(fn (DragonTreasure $treasure) => (
            $this->microMapper->map($treasure, DragonTreasureApi::class, [
                MicroMapperInterface::MAX_DEPTH => 0,
            ])
        ), $from->getPublishedDragonTreasures()->getValues());
        $to->flameThrowingDistance = rand(1, 10);

        return $to;
    }
}