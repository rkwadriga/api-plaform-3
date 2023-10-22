<?php declare(strict_types=1);
/**
 * Created 2023-10-22 06:40:46
 * Author rkwadriga
 */

namespace App\Tests\Functional;

use App\Factory\DragonTreasureFactory;
use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

/**
 * Run: symt tests/Functional/DragonTreasureResourceTest.php
 */
class DragonTreasureResourceTest extends KernelTestCase
{
    use HasBrowser;
    use ResetDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        UserFactory::createMany(2);
        DragonTreasureFactory::createMany(5, fn () => [
            'owner' => UserFactory::random(),
        ]);
    }

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
}