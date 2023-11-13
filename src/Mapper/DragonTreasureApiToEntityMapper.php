<?php declare(strict_types=1);
/**
 * Created 2023-11-10 15:53:00
 * Author rkwadriga
 */

namespace App\Mapper;

use App\ApiResource\DragonTreasureApi;
use App\Entity\DragonTreasure;
use App\Repository\DragonTreasureRepository;
use Exception;
use Symfony\Bundle\SecurityBundle\Security;
use Symfonycasts\MicroMapper\AsMapper;
use Symfonycasts\MicroMapper\MapperInterface;

#[AsMapper(from: DragonTreasureApi::class, to: DragonTreasure::class)]
class DragonTreasureApiToEntityMapper implements MapperInterface
{
    public function __construct(
        private readonly DragonTreasureRepository $dragonTreasureRepository,
        private readonly Security $security
    ) {}

    /**
     * @param DragonTreasureApi $from
     * @param string $toClass
     * @param array $context
     * @return DragonTreasure
     */
    public function load(object $from, string $toClass, array $context): object
    {
        return $this->getEntity($from);
    }

    /**
     * @param DragonTreasureApi $from
     * @param DragonTreasure $to
     * @param array $context
     * @return DragonTreasure
     */
    public function populate(object $from, object $to, array $context): object
    {
        $to
            ->setDescription($from->description)
            ->setValue($from->value)
            ->setCoolFactor($from->coolFactor)
            ->setIsPublished($from->isPublished)
        ;

        return $to;
    }

    private function getEntity(DragonTreasureApi $dto): DragonTreasure
    {
        $entity = $dto->id !== null ? $this->dragonTreasureRepository->find($dto->id) : new DragonTreasure($dto->name);
        if ($entity === null) {
            throw new Exception('DragonTreasure not found');
        }

        if ($dto->owner !== null) {
            // @TODO set owner!
            $entity->setOwner($this->security->getUser());
        } else {
            $entity->setOwner($this->security->getUser());
        }

        return $entity;
    }
}