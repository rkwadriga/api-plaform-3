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
    provider: DailyQuestStateProvider::class, // For Get and GetCollection operations
    processor: DailyQuestStateProcessor::class // For other operations
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

    /**
     * @var array<DragonTreasure>
     */
    public array $treasures;

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