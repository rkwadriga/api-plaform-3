<?php declare(strict_types=1);

namespace App\Validator;

use App\ApiResource\UserApi;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use LogicException;

class IsValidOwnerValidator extends ConstraintValidator
{
    public function __construct(
        private readonly Security $security
    ) {
    }

    /**
     * @param UserApi $value
     * @param IsValidOwner $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if ($value === null) {
            return;
        }

        /** @var ?User $user */
        $user = $this->security->getUser();
        if ($user === null) {
            throw new LogicException('IsValidOwnerValidator should only be used when a user is logged in');
        }

        if ($value->id === $user->getId() || $this->security->isGranted('ROLE_ADMIN')) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->addViolation();
    }
}
