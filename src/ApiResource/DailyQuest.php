<?php declare(strict_types=1);
/**
 * Created 2023-11-02 11:31:57
 * Author rkwadriga
 */

namespace App\ApiResource;

use ApiPlatform\Metadata as Metadata;
use App\Entity\DragonTreasure;
use App\Enum\DailyQuestStatusEnum;
use App\State\DailyQuestStateProcessor;
use App\State\DailyQuestStateProvider;
use DateTimeInterface;
use Symfony\Component\Serializer\Annotation\Ignore;

#[Metadata\ApiResource(
    shortName: 'Quest',
    operations: [
        new Metadata\GetCollection(),
        new Metadata\Get(),
        new Metadata\Patch(),
    ],
    paginationItemsPerPage: 5, // For Get and GetCollection operations
    provider: DailyQuestStateProvider::class,
    processor: DailyQuestStateProcessor::class// For other operations
)]
class DailyQuest
{
    #[Ignore]
    public DateTimeInterface $day;

    public string $questName;

    public string $description;

    public int $difficultyLevel;

    public DailyQuestStatusEnum $status;

    public DateTimeInterface $lastUpdated;

    #[Metadata\ApiProperty(genId: false)]
    public QuestTreasure $treasure;

    public function __construct(
        DateTimeInterface $day
    ) {
        $this->day = $day;
    }

    #[Metadata\ApiProperty(
        readable: false,
        identifier: true
    )]
    public function getDayString(): string
    {
        return $this->day->format('Y-m-d');
    }
}