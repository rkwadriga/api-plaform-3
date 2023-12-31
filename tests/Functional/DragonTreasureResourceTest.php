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
use App\Factory\NotificationFactory;
use App\Factory\UserFactory;
use App\Repository\NotificationRepository;
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
            'isPublished' => true,
        ]);

        DragonTreasureFactory::createOne([
            'owner' => UserFactory::createOne(),
            'isPublished' => false,
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
                'isMine',
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
     * Run: symt --filter=testGetUnpublishedTreasure
     */
    public function testGetUnpublishedTreasure(): void
    {
        $dragonTreasure = DragonTreasureFactory::createOne([
            'owner' => UserFactory::createOne(),
            'isPublished' => false,
        ]);

        $this->browser()
            ->get('/api/treasures/' . $dragonTreasure->getId())
            ->assertStatus(Response::HTTP_NOT_FOUND)
        ;
    }

    /**
     * Run: symt --filter=testPostWithoutOwnerCreateTreasure
     */
    public function testPostWithoutOwnerCreateTreasure(): void
    {
        $user = UserFactory::createOne();

        $this->browser()
            ->asUser($user)
            ->post('/api/treasures', [
                'name' => 'A shiny thing',
                'description' => 'It sparkles when I wave it in the air.',
                'value' => 1000,
                'coolFactor' => 5,
            ])
            ->assertStatus(Response::HTTP_CREATED)
        ;
    }

    /**
     * Run: symt --filter=testPostWithOwnerCreateTreasure
     */
    public function testPostWithOwnerCreateTreasure(): void
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
            ->assertJsonMatches('name', 'A shiny thing')
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
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ;
    }

    /**
     * Run: symt --filter=testAdminCanPatchToEditTreasure
     */
    public function testAdminCanPatchToEditTreasure(): void
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
                'isPublished' => true,
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('value', 12345)
            ->assertJsonMatches('isPublished', true)
        ;
    }

    /**
     * Run: symt --filter=testAdminCanPatchWithNewOwnerToEditTreasure
     */
    public function testAdminCanPatchWithNewOwnerToEditTreasure(): void
    {
        $user = UserFactory::createOne();
        $newOwner = UserFactory::createOne();
        $treasure = DragonTreasureFactory::createOne([
            'isPublished' => false,
            'owner' => $user,
        ]);
        $admin = UserFactory::new()->asAdmin()->create();

        $newOwnerUri = '/api/users/' . $newOwner->getId();

        $this->browser()
            ->asUser($admin)
            ->patch('/api/treasures/' . $treasure->getId(), [
                'owner' => $newOwnerUri,
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('owner', $newOwnerUri)
        ;
    }

    /**
     * Run: symt --filter=testAdminCanSeeUnpublishedTreasure
     */
    public function testAdminCanSeeUnpublishedTreasure(): void
    {
        $user = UserFactory::createOne();
        $treasure = DragonTreasureFactory::createOne([
            'isPublished' => false,
            'owner' => $user,
        ]);
        $admin = UserFactory::new()->asAdmin()->create();

        $this->browser()
            ->asUser($admin)
            ->get('/api/treasures/' . $treasure->getId())
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('isPublished', false)
        ;
    }

    /**
     * Run: symt --filter=testOwnerCanSeeUnpublishedTreasure
     */
    public function testOwnerCanSeeUnpublishedTreasure(): void
    {
        $user = UserFactory::createOne();
        $treasure = DragonTreasureFactory::createOne([
            'isPublished' => false,
            'owner' => $user,
        ]);

        $this->browser()
            ->asUser($user, [ApiToken::SCOPE_TREASURE_EDIT])
            ->get('/api/treasures/' . $treasure->getId())
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('isPublished', false)
        ;
    }

    /**
     * Run: symt --filter=testAdminCanSeeIsPublishedFieldFromGetItemRequest
     */
    public function testAdminCanSeeIsPublishedFieldFromGetItemRequest(): void
    {
        $user = UserFactory::createOne();
        $treasure = DragonTreasureFactory::createOne([
            'isPublished' => true,
            'owner' => $user,
        ]);
        $admin = UserFactory::new()->asAdmin()->create();

        $this->browser()
            ->asUser($admin)
            ->get('/api/treasures/' . $treasure->getId())
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('isPublished', true)
        ;
    }

    /**
     * Run: symt --filter=testAdminCanSeeIsPublishedFieldFromGetCollectionRequest
     */
    public function testAdminCanSeeIsPublishedFieldFromGetCollectionRequest(): void
    {
        DragonTreasureFactory::createMany(3, [
            'isPublished' => true,
            'owner' => UserFactory::new(),
        ]);
        $admin = UserFactory::new()->asAdmin()->create();

        $this->browser()
            ->asUser($admin)
            ->get('/api/treasures')
            ->assertJson()
            ->assertJsonMatches('"hydra:totalItems"', 3)
            ->assertJsonMatches('keys("hydra:member"[0])', [
                '@id',
                '@type',
                'id',
                'owner',
                'name',
                'description',
                'value',
                'coolFactor',
                'isPublished',
                'shortDescription',
                'plunderedAtAgo',
                'isMine',
            ])
        ;
    }

    /**
     * Run: symt --filter=testOwnerCanSeeIsPublishedFieldPatchToEditTreasure
     */
    public function testOwnerCanSeeIsPublishedFieldPatchToEditTreasure(): void
    {
        $user = UserFactory::createOne();
        $treasure = DragonTreasureFactory::createOne([
            'isPublished' => true,
            'owner' => $user,
        ]);

        $this->browser()
            ->asUser($user, [ApiToken::SCOPE_TREASURE_EDIT])
            ->patch('/api/treasures/' . $treasure->getId(), [
                'value' => 12345,
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('value', 12345)
            ->assertJsonMatches('isPublished', true)
        ;
    }

    /**
     * Run: symt --filter=testOwnerCanSeeIsPublishedAndIsMineFields
     */
    public function testOwnerCanSeeIsPublishedAndIsMineFields(): void
    {
        $user = UserFactory::createOne();
        $treasure = DragonTreasureFactory::createOne([
            'isPublished' => true,
            'owner' => $user,
        ]);

        $this->browser()
            ->asUser($user, [ApiToken::SCOPE_TREASURE_EDIT])
            ->patch('/api/treasures/' . $treasure->getId(), [
                'value' => 12345,
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('isPublished', true)
            ->assertJsonMatches('isMine', true)
        ;
    }

    /**
     * Run: symt --filter=testNotOwnerCanNotSeeIsMineField
     */
    public function testNotOwnerCanNotSeeIsMineField(): void
    {
        $user = UserFactory::createOne();
        $otherUser = UserFactory::createOne();
        $treasure = DragonTreasureFactory::createOne([
            'isPublished' => true,
            'owner' => $otherUser,
        ]);

        $this->browser()
            ->asUser($user)
            ->get('/api/treasures/' . $treasure->getId())
            ->assertJsonMatches('isMine', false)
        ;
    }

    /**
     * Run: symt --filter=testPublishTreasure
     */
    public function testPublishTreasure(): void
    {
        $user = UserFactory::createOne();
        $treasure = DragonTreasureFactory::createOne([
            'isPublished' => false,
            'owner' => $user,
        ]);

        $this->browser()
            ->asUser($user, [ApiToken::SCOPE_TREASURE_EDIT])
            ->patch('/api/treasures/' . $treasure->getId(), [
                'isPublished' => true,
            ])
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('isPublished', true)
        ;

        NotificationFactory::repository()->assert()->count(1);
    }
}