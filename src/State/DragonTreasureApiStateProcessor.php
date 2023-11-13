<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\DragonTreasureApi;
use App\Entity\Notification;
use App\Repository\DragonTreasureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class DragonTreasureApiStateProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly EntityClassDtoStateProcessor $innerProcessor,
        private readonly DragonTreasureRepository $dragonTreasureRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    /**
     * @param DragonTreasureApi $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     * @return object|null
     * @throws Exception
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        /** @var DragonTreasureApi $result */
        $result = $this->innerProcessor->process($data, $operation, $uriVariables, $context);

        $previousData = $context['previous_data'] ?? null;
        if ($previousData instanceof DragonTreasureApi
            && $data->isPublished
            && $previousData->isPublished !== $result->isPublished
        ) {
            $entity = $this->dragonTreasureRepository->find($result->id);
            if ($entity === null) {
                throw new Exception ('The dragon treasure #%s was not found', $result->id);
            }

            $notification = new Notification();
            $notification
                ->setDragonTreasure($entity)
                ->setMessage(sprintf('Treasure #%s has been published!', $entity->getId()))
            ;
            $this->entityManager->persist($notification);
            $this->entityManager->flush();
        }

        return $result;
    }
}
