<?php

namespace App\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\DailyQuest;
use App\Enum\DailyQuestStatusEnum;
use DateTime;
use DateTimeImmutable;

class DailyQuestStateProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $quests = $this->createQuests();

        if ($operation instanceof CollectionOperationInterface) {
            return $quests;
        }

        return $quests[$uriVariables['dayString']] ?? null;
    }

    private function createQuests(): array
    {
        $quests = [];
        for ($i = 0; $i < 50; $i++) {
            $quest = new DailyQuest(new DateTimeImmutable(sprintf('- %d days', $i)));
            $quest->questName = sprintf('Quest %d', $i);
            $quest->description = sprintf('Description %d', $i);
            $quest->difficultyLevel = $i % 10;
            $quest->status = $i % 2 === 0 ? DailyQuestStatusEnum::ACTIVE : DailyQuestStatusEnum::COMPLETED;
            $quest->lastUpdated = new DateTimeImmutable(sprintf('-%s days', rand(1, 100)));

            $quests[$quest->getDayString()] = $quest;
        }
        return $quests;
    }
}
