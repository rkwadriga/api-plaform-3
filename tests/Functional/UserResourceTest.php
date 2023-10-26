<?php declare(strict_types=1);
/**
 * Created 2023-10-26 22:28:13
 * Author rkwadriga
 */

namespace App\Tests\Functional;

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
}