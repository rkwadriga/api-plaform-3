<?php declare(strict_types=1);

namespace App\Validator;

use App\ApiResource\UserApi;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TreasureAllowedOwnerChangeValidator extends ConstraintValidator
{
    public function __construct(
        private readonly Security $security
    ) {
    }

    /**
     * @param UserApi $value
     * @param TreasureAllowedOwnerChange $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if ($value === null) {
            return;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        $allOwnersCorrect = true;
        foreach ($value->dragonTreasures as $dragonTreasureApi) {
            $originalOwner = $dragonTreasureApi->owner;
            if ($originalOwner !== null && $originalOwner->id !== $value->id) {
                $allOwnersCorrect = false;
                break;
            }
        }

        if ($allOwnersCorrect) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->addViolation();
    }
}
