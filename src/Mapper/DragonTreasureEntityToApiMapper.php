<?php declare(strict_types=1);
/**
 * Created 2023-11-09 17:21:07
 * Author rkwadriga
 */

namespace App\Mapper;

use App\ApiResource\DragonTreasureApi;
use App\ApiResource\UserApi;
use App\Entity\DragonTreasure;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: DragonTreasure::class, to: DragonTreasureApi::class)]
class DragonTreasureEntityToApiMapper implements MapperInterface
{
    public function __construct(
        private readonly MicroMapperInterface $microMapper
    ) {
    }

    /**
     * @param DragonTreasure $from
     * @param string $toClass
     * @param array $context
     * @return DragonTreasureApi
     */
    public function load(object $from, string $toClass, array $context): object
    {
        $dto = new DragonTreasureApi();
        $dto->id = $from->getId();
        $dto->owner = $from->getOwner() ? $this->microMapper->map($from->getOwner(), UserApi::class) : null;

        return $dto;
    }

    /**
     * @param DragonTreasure $from
     * @param DragonTreasureApi $to
     * @param array $context
     * @return DragonTreasureApi
     */
    public function populate(object $from, object $to, array $context): object
    {
        $to->name = $from->getName();

        return $to;
    }
}