<?php declare(strict_types=1);
/**
 * Created 2023-10-22 06:40:46
 * Author rkwadriga
 */

namespace App\Tests\Functional;

use App\Factory\DragonTreasureFactory;
use App\Factory\UserFactory;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * Run: symt tests/Functional/DragonTreasureResourceTest.php
 */
class DragonTreasureResourceTest extends ApiTestCaseAbstract
{
    use ResetDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        UserFactory::createMany(2);
        DragonTreasureFactory::createMany(5, fn () => [
            'owner' => UserFactory::random(),
        ]);
    }

    /**
     * Run: symt --filter=testGetCollectionOfTreasures
     */
    public function testGetCollectionOfTreasures(): void
    {
        $this->browser()
            ->get('/api/treasures')
            ->assertJson()
            ->assertJsonMatches('"hydra:totalItems"', 5)
            ->assertJsonMatches('keys("hydra:member"[0])', [
                '@id',
                '@type',
                'id',
                'owner',
                'name',
                'description',
                'value',
                'coolFactor',
                'shortDescription',
                'plunderedAtAgo',
            ])
            /*->use(function (\Zenstruck\Browser\Json $json) {
                dump($json->search('keys("hydra:member"[0])'));
            })*/
        ;
    }

    /**
     * Run: symt --filter=testPostCreateEmptyTreasure
     */
    public function testPostCreateEmptyTreasure(): void
    {
        $user = UserFactory::createOne();

        $this->browser()
            ->actingAs($user)
            ->post('/api/treasures', [
                'json' => [],
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ;
    }

    /**
    * Run: symt --filter=testPostCreateTreasure
    */
    public function testPostCreateTreasure(): void
    {
        $user = UserFactory::createOne();

        $this->browser()
            ->actingAs($user)
            ->post('/api/treasures', [
                'json' => [
                    'name' => 'A shiny thing',
                    'description' => 'It sparkles when I wave it in the air.',
                    'value' => 1000,
                    'coolFactor' => 5,
                    'owner' => '/api/users/' . $user->getId(),
                ],
            ])
            ->assertStatus(Response::HTTP_CREATED)
            ->dump()
        ;
    }
}