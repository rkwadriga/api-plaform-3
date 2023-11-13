<?php declare(strict_types=1);
/**
 * Created 2023-11-09 17:21:07
 * Author rkwadriga
 */

namespace App\Mapper;

use App\ApiResource\DragonTreasureApi;
use App\ApiResource\UserApi;
use App\Entity\DragonTreasure;
use Symfony\Bundle\SecurityBundle\Security;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;
use Symfonycasts\MicroMapper\MicroMapperInterface;

#[AsMapper(from: DragonTreasure::class, to: DragonTreasureApi::class)]
class DragonTreasureEntityToApiMapper implements MapperInterface
{
    public function __construct(
        private readonly MicroMapperInterface $microMapper,
        private readonly Security $security
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
        $dto->owner = $from->getOwner()
            ? $this->microMapper->map($from->getOwner(), UserApi::class, [MicroMapperInterface::MAX_DEPTH => 1])
            : null;

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
        $currentUser = $this->security->getUser();

        $to->name = $from->getName();
        $to->description = $from->getDescription();
        $to->value = $from->getValue();
        $to->coolFactor = $from->getCoolFactor();
        $to->shortDescription = $from->getShortDescription();
        $to->plunderedAtAgo = $from->getPlunderedAtAgo();
        $to->isPublished = $from->getIsPublished();
        $to->isMine = $currentUser !== null && $currentUser === $from->getOwner();

        return $to;
    }
}