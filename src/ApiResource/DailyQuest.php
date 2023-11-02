<?php declare(strict_types=1);
/**
 * Created 2023-11-02 11:31:57
 * Author rkwadriga
 */

namespace App\ApiResource;

use ApiPlatform\Metadata as Metadata;
use App\Enum\DailyQuestStatusEnum;
use App\State\DailyQuestStateProvider;
use DateTimeInterface;
use Symfony\Component\Serializer\Annotation\Ignore;

#[Metadata\ApiResource(
    shortName: 'Quest',
    provider: DailyQuestStateProvider::class
)]
class DailyQuest
{
    #[Ignore]
    public DateTimeInterface $day;

    public string $questName;

    public string $description;

    public int $difficultyLevel;

    public DailyQuestStatusEnum $status;

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