<?php declare(strict_types=1);

namespace App\DataFixtures;

use App\Factory\ApiTokenFactory;
use App\Factory\DragonTreasureFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        UserFactory::createMany(10);

        DragonTreasureFactory::createMany(40, fn () => [
            'owner' => UserFactory::random(),
        ]);

        ApiTokenFactory::createMany(30, fn () => [
            'ownedBy' => UserFactory::random(),
        ]);
    }
}
