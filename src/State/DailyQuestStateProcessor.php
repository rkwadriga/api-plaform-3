<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\DailyQuest;
use DateTimeImmutable;

class DailyQuestStateProcessor implements ProcessorInterface
{
    /**
     * @param DailyQuest $data
     * @param Operation $operation
     * @param array $uriVariables
     * @param array $context
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $data->lastUpdated = new DateTimeImmutable();
    }
}
