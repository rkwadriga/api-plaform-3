<?php declare(strict_types=1);

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class TreasureAllowedOwnerChange extends Constraint
{
    public $message = 'You can not change the treasure owner!';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
