<?php declare(strict_types=1);
/**
 * Created 2023-10-22 06:40:46
 * Author rkwadriga
 */

namespace App\Tests\Functional;

use App\Entity\ApiToken;
use App\Entity\User;
use App\Factory\ApiTokenFactory;
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

        DragonTreasureFactory::createMany(5, fn () => [
            'owner' => UserFactory::random(),
        ]);

        ApiTokenFactory::createOne([
            'ownedBy' => $this->user,
            'scopes' => [ApiToken::SCOPE_TREASURE_CREATE],
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
        $this->browser()
            ->actingAs($this->user)
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
        $this->browser()
            ->actingAs($this->user)
            ->post('/api/treasures', [
                'json' => [
                    'name' => 'A shiny thing',
                    'description' => 'It sparkles when I wave it in the air.',
                    'value' => 1000,
                    'coolFactor' => 5,
                    'owner' => '/api/users/' . $this->user->getId(),
                ],
            ])
            ->assertStatus(Response::HTTP_CREATED)
        ;
    }

    /**
     * Run: symt --filter=testPostCreateEmptyTreasureWithApiToken
     */
    public function testPostCreateEmptyTreasureWithApiToken(): void
    {
        $token = ApiTokenFactory::random();

        $this->browser()
            ->post('/api/treasures', [
                'json' => [],
                'headers' => [
                    'Authorization' => 'Bearer ' . $token->getToken(),
                ],
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ;
    }

    /**
     * Run: symt --filter=testPostCreateEmptyTreasureWithApiTokenDeniedWithoutScope
     */
    public function testPostCreateEmptyTreasureWithApiTokenDeniedWithoutScope(): void
    {
        $token = ApiTokenFactory::random();

        $this->browser()
            ->post('/api/treasures', [
                'json' => [],
                'headers' => [
                    'Authorization' => 'Bearer ' . $token->getToken(),
                ],
            ])
            ->assertStatus(Response::HTTP_FORBIDDEN)
        ;
    }
}