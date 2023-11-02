<?php declare(strict_types=1);
/**
 * Created 2023-11-02 13:48:37
 * Author rkwadriga
 */

namespace App\Tests\Functional;

use App\Enum\DailyQuestStatusEnum;
use App\Factory\DragonTreasureFactory;
use App\Factory\UserFactory;
use DateTime;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * Run: symt tests/Functional/DailyQuestResourceTest.php
 */
class DailyQuestResourceTest extends ApiTestCaseAbstract
{
    use ResetDatabase;

    use Factories;

    /**
     * Run: symt --filter=testPatchCanUpdateStatus
     */
    public function testPatchCanUpdateStatus(): void
    {
        DragonTreasureFactory::createMany(5, [
            'owner' => UserFactory::new(),
        ]);

        $yesterday = new DateTime('-2 days');
        $this->browser()
            ->patch('/api/quests/' . $yesterday->format('Y-m-d'), [
                'status' => DailyQuestStatusEnum::COMPLETED->value,
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('status', DailyQuestStatusEnum::COMPLETED->value)
        ;
    }
}