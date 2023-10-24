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

    /**
     * Run: symt --filter=testGetCollectionOfTreasuresSuccess
     */
    public function testGetCollectionOfTreasuresSuccess(): void
    {
        DragonTreasureFactory::createMany(5, fn () => [
            'owner' => $this->getUser(),
        ]);

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
     * Run: symt --filter=testPostCreateEmptyTreasureSuccess
     */
    public function testPostCreateEmptyTreasureSuccess(): void
    {
        $this->browser()
            ->actingAs($this->getUser())
            ->post('/api/treasures', [
                'json' => [],
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ;
    }

    /**
    * Run: symt --filter=testPostCreateTreasureSuccess
    */
    public function testPostCreateTreasureSuccess(): void
    {
        $this->browser()
            ->actingAs($this->getUser())
            ->post('/api/treasures', [
                'json' => [
                    'name' => 'A shiny thing',
                    'description' => 'It sparkles when I wave it in the air.',
                    'value' => 1000,
                    'coolFactor' => 5,
                    'owner' => '/api/users/' . $this->getUser()->getId(),
                ],
            ])
            ->assertStatus(Response::HTTP_CREATED)
        ;
    }

    /**
     * Run: symt --filter=testPostCreateEmptyTreasureWithApiTokenSuccess
     */
    public function testPostCreateEmptyTreasureWithApiTokenSuccess(): void
    {
        $token = ApiTokenFactory::createOne([
            'ownedBy' => $this->getUser(),
            'scopes' => [ApiToken::SCOPE_TREASURE_CREATE],
        ]);

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
        $token = ApiTokenFactory::createOne([
            'ownedBy' => $this->getUser(),
            'scopes' => [],
        ]);

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

    /**
     * Run: symt --filter=testPatchToUpdateTreasureWithApiTokenSuccess
     */
    public function testPatchToUpdateTreasureWithApiTokenSuccess(): void
    {
        $treasure = DragonTreasureFactory::createOne([
            'owner' => $this->getUser(),
        ]);
        $token = ApiTokenFactory::createOne([
            'ownedBy' => $this->getUser(),
            'scopes' => [ApiToken::SCOPE_TREASURE_EDIT],
        ]);

        $this->browser()
            ->patch('/api/treasures/' . $treasure->getId(), [
                'json' => [
                    'value' => 12345,
                ],
                'headers' => [
                    'Authorization' => 'Bearer ' . $token->getToken(),
                ],
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('value', 12345)
        ;
    }

    /**
     * Run: symt --filter=testPatchToUpdateTreasureWithApiTokenWithAnotherUser
     */
    public function testPatchToUpdateTreasureWithApiTokenWithAnotherUser(): void
    {
        $treasure = DragonTreasureFactory::createOne([
            'owner' => $this->getUser(),
        ]);
        $notOwner = UserFactory::createOne();
        $token = ApiTokenFactory::createOne([
            'ownedBy' => $notOwner,
            'scopes' => [ApiToken::SCOPE_TREASURE_EDIT],
        ]);

        $this->browser()
            ->patch('/api/treasures/' . $treasure->getId(), [
                'json' => [
                    'value' => 12345,
                ],
                'headers' => [
                    'Authorization' => 'Bearer ' . $token->getToken(),
                ],
            ])
            ->assertStatus(Response::HTTP_FORBIDDEN)
        ;
    }

    /**
     * Run: symt --filter=testPatchToUpdateTreasureWithNewOwner
     */
    public function testPatchToUpdateTreasureWithNewOwner(): void
    {
        $treasure = DragonTreasureFactory::createOne([
            'owner' => $this->getUser(),
        ]);
        $notOwner = UserFactory::createOne();
        $token = ApiTokenFactory::createOne([
            'ownedBy' => $this->getUser(),
            'scopes' => [ApiToken::SCOPE_TREASURE_EDIT],
        ]);

        $this->browser()
            ->actingAs($treasure->getOwner())
            ->patch('/api/treasures/' . $treasure->getId(), [
                'json' => [
                    'owner' => '/api/users/' . $notOwner->getId(),
                ],
                'headers' => [
                    'Authorization' => 'Bearer ' . $token->getToken(),
                ],
            ])
            ->assertStatus(Response::HTTP_FORBIDDEN)
        ;
    }
}