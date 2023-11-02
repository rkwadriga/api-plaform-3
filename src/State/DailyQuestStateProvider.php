<?php

namespace App\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\Pagination\TraversablePaginator;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\DailyQuest;
use App\ApiResource\QuestTreasure;
use App\Enum\DailyQuestStatusEnum;
use App\Repository\DragonTreasureRepository;
use ArrayIterator;
use DateTimeImmutable;

class DailyQuestStateProvider implements ProviderInterface
{
    public function __construct(
        private readonly DragonTreasureRepository $dragonTreasureRepository,
        private readonly Pagination $pagination
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {


        if ($operation instanceof CollectionOperationInterface) {
            $currentPage = $this->pagination->getPage($context);
            $itemsPerPage = $this->pagination->getLimit($operation, $context);
            $offset = $this->pagination->getOffset($operation, $context);
            $totalItems = $this->countTotalRequests();

            return new TraversablePaginator(
                new ArrayIterator($this->createQuests($offset, $itemsPerPage)),
                $currentPage,
                $itemsPerPage,
                $totalItems
            );
        }

        $quests = $this->createQuests(0, $this->countTotalRequests());

        return $quests[$uriVariables['dayString']] ?? null;
    }

    private function createQuests(int $offset, int $limit): array
    {
        $treasures = $this->dragonTreasureRepository->findBy([], [], 10);

        $quests = [];
        for ($i = $offset; $i < ($offset + $limit); $i++) {
            $quest = new DailyQuest(new DateTimeImmutable(sprintf('- %d days', $i)));
            $quest->questName = sprintf('Quest %d', $i);
            $quest->description = sprintf('Description %d', $i);
            $quest->difficultyLevel = $i % 10;
            $quest->status = $i % 2 === 0 ? DailyQuestStatusEnum::ACTIVE : DailyQuestStatusEnum::COMPLETED;
            $quest->lastUpdated = new DateTimeImmutable(sprintf('-%s days', rand(1, 100)));
            $randomTreasure = $treasures[array_rand($treasures)];
            $quest->treasure = new QuestTreasure(
                $randomTreasure->getName(),
                $randomTreasure->getValue(),
                $randomTreasure->getCoolFactor()
            );

            $quests[$quest->getDayString()] = $quest;
        }
        return $quests;
    }

    private function countTotalRequests(): int
    {
        return 50;
    }
}
