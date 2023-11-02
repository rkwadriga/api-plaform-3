<?php declare(strict_types=1);
/**
 * Created 2023-11-02 15:03:44
 * Author rkwadriga
 */

namespace App\ApiResource;

class QuestTreasure
{
    public function __construct(
        public string $name,
        public int $value,
        public int $coolFactor
    ) {
    }
}