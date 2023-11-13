<?php

namespace App\Security\Voter;

use App\ApiResource\DragonTreasureApi;
use App\Entity\ApiToken;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class DragonTreasureApiVoter extends Voter
{
    public const EDIT = 'EDIT';

    public function __construct(
        private readonly Security $security
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::EDIT && $subject instanceof DragonTreasureApi;
    }

    /**
     * @param string $attribute
     * @param DragonTreasureApi $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof User) {
            return false;
        }

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        if (!$this->security->isGranted(ApiToken::SCOPE_TREASURE_EDIT)) {
            return false;
        }

        if ($subject->owner?->id === $user->getId()) {
            return true;
        }

        return false;
    }
}
