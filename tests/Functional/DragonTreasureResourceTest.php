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
     * Run: symt --filter=testGetCollectionOfTreasures
     */
    public function testGetCollectionOfTreasures(): void
    {
        DragonTreasureFactory::createMany(5, fn () => [
            'owner' => UserFactory::createOne(),
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
     * Run: symt --filter=testPostCreateEmptyTreasure
     */
    public function testPostCreateEmptyTreasure(): void
    {
        $user = UserFactory::createOne();

        $this->browser()
            ->asUser($user)
            ->post('/api/treasures')
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
            ->asUser($user)
            ->post('/api/treasures', [
                'name' => 'A shiny thing',
                'description' => 'It sparkles when I wave it in the air.',
                'value' => 1000,
                'coolFactor' => 5,
                'owner' => '/api/users/' . $user->getId(),
            ])
            ->assertStatus(Response::HTTP_CREATED)
        ;
    }

    /**
     * Run: symt --filter=testDeniedWithoutScopePostCreateEmptyTreasure
     */
    public function testDeniedWithoutScopePostCreateEmptyTreasure(): void
    {
        $user = UserFactory::createOne();

        $this->browser()
            ->asUser($user, [])
            ->post('/api/treasures')
            ->assertStatus(Response::HTTP_FORBIDDEN)
        ;
    }

    /**
     * Run: symt --filter=testWithTreasureEditScopePatchToUpdateTreasure
     */
    public function testWithTreasureEditScopePatchToUpdateTreasure(): void
    {
        $user = UserFactory::createOne();
        $treasure = DragonTreasureFactory::createOne([
            'owner' => $user,
        ]);

        $this->browser()
            ->asUser($user, [ApiToken::SCOPE_TREASURE_EDIT])
            ->patch('/api/treasures/' . $treasure->getId(), [
                'value' => 12345,
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('value', 12345)
        ;
    }

    /**
     * Run: symt --filter=testWithAnotherUserPatchToUpdateTreasure
     */
    public function testWithAnotherUserPatchToUpdateTreasure(): void
    {
        $user = UserFactory::createOne();
        $treasure = DragonTreasureFactory::createOne([
            'owner' => $user,
        ]);
        $notOwner = UserFactory::createOne();

        $this->browser()
            ->asUser($notOwner, [ApiToken::SCOPE_TREASURE_EDIT])
            ->patch('/api/treasures/' . $treasure->getId(), [
                'value' => 12345,
            ])
            ->assertStatus(Response::HTTP_FORBIDDEN)
        ;
    }

    /**
     * Run: symt --filter=testWithNewOwnerPatchToUpdateTreasure
     */
    public function testWithNewOwnerPatchToUpdateTreasure(): void
    {
        $user = UserFactory::createOne();
        $treasure = DragonTreasureFactory::createOne([
            'owner' => $user,
        ]);
        $notOwner = UserFactory::createOne();

        $this->browser()
            ->asUser($user, [ApiToken::SCOPE_TREASURE_EDIT])
            ->patch('/api/treasures/' . $treasure->getId(), [
                'owner' => '/api/users/' . $notOwner->getId(),
            ])
            ->assertStatus(Response::HTTP_FORBIDDEN)
        ;
    }

    /**
     * Run: symt --filter=testAdminCanPatchToEditTreasure
     */
    public function testAdminCanSeeIsPublishedFieldPatchToEditTreasure(): void
    {
        $user = UserFactory::createOne();
        $treasure = DragonTreasureFactory::createOne([
            'isPublished' => false,
            'owner' => $user,
        ]);
        $admin = UserFactory::new()->asAdmin()->create();

        $this->browser()
            ->asUser($admin)
            ->patch('/api/treasures/' . $treasure->getId(), [
                'value' => 12345,
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('value', 12345)
            ->assertJsonMatches('isPublished', false)
        ;
    }

    /**
     * Run: symt --filter=testOwnerCanSeeIsPublishedFieldPatchToEditTreasure
     */
    public function testOwnerCanSeeIsPublishedFieldPatchToEditTreasure(): void
    {
        $user = UserFactory::createOne();
        $treasure = DragonTreasureFactory::createOne([
            'isPublished' => false,
            'owner' => $user,
        ]);

        $this->browser()
            ->asUser($user, [ApiToken::SCOPE_TREASURE_EDIT])
            ->patch('/api/treasures/' . $treasure->getId(), [
                'value' => 12345,
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('value', 12345)
            ->assertJsonMatches('isPublished', false)
        ;
    }
}