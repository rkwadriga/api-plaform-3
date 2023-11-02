<?php declare(strict_types=1);
/**
 * Created 2023-11-02 11:58:05
 * Author rkwadriga
 */

namespace App\Enum;

enum DailyQuestStatusEnum: string
{
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
}
