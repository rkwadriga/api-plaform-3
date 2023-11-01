<?php declare(strict_types=1);

namespace App\Validator;

use App\Entity\DragonTreasure;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TreasureAllowedOwnerChangeValidator extends ConstraintValidator
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Security $security
    ) {
    }

    /**
     * @param array<DragonTreasure> $value
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
        $unitOfWork = $this->entityManager->getUnitOfWork();
        foreach ($value as $dragonTreasure) {
            $originalData = $unitOfWork->getOriginalEntityData($dragonTreasure);
            if (isset($originalData['owner']) && $originalData['owner'] !== $dragonTreasure->getOwner()) {
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
