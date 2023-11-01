<?php declare(strict_types=1);
/**
 * Created 2023-10-26 22:28:13
 * Author rkwadriga
 */

namespace App\Tests\Functional;

use App\Factory\DragonTreasureFactory;
use App\Factory\UserFactory;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * Run: symt tests/Functional/UserResourceTest.php
 */
class UserResourceTest extends ApiTestCaseAbstract
{
    use ResetDatabase;

    /**
     * Run: symt --filter=testPostToCreateUser
     */
    public function testPostToCreateUser(): void
    {
        $this->browser()
            ->post('/api/users', [
                'email' => 'draggin_in_the_morning@coffee.com',
                'username' => 'draggin_in_the_morning',
                'password' => '12345678',
            ])
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonMatches('email', 'draggin_in_the_morning@coffee.com')
            ->post('/api/login', [
                'email' => 'draggin_in_the_morning@coffee.com',
                'password' => '12345678',
            ])
            ->assertSuccessful()
        ;
    }

    /**
     * Run: symt --filter=testPatchToUpdateUser
     */
    public function testPatchToUpdateUser(): void
    {
        $user = UserFactory::createOne();

        $this->browser()
            ->asUser($user)
            ->patch('/api/users/' . $user->getId(), [
                'username' => 'changed',
            ])
            ->assertStatus(Response::HTTP_OK)
        ;
    }

    /**
     * Run: symt --filter=testTreasuresCanNotBeStolen
     */
    public function testTreasuresCanNotBeStolen(): void
    {
        $user = UserFactory::createOne();
        $otherUser = UserFactory::createOne();
        $dragonTreasure = DragonTreasureFactory::createOne(['owner' => $otherUser]);

        $this->browser()
            ->asUser($user)
            ->patch('/api/users/' . $user->getId(), [
                'username' => 'changed',
                'dragonTreasures' => [
                    '/api/treasures/' . $dragonTreasure->getId(),
                ],
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ;
    }

    /**
     * Run: symt --filter=testAdminCanChangeTheTreasuresOwner
     */
    public function testAdminCanChangeTheTreasuresOwner(): void
    {
        $admin = UserFactory::new()->asAdmin()->create();
        $user = UserFactory::createOne();
        $otherUser = UserFactory::createOne();
        $dragonTreasure = DragonTreasureFactory::createOne(['owner' => $otherUser]);

        $this->browser()
            ->asUser($admin)
            ->patch('/api/users/' . $user->getId(), [
                'username' => 'changed',
                'dragonTreasures' => [
                    '/api/treasures/' . $dragonTreasure->getId(),
                ],
            ])
            ->assertStatus(Response::HTTP_OK)
        ;
    }
}