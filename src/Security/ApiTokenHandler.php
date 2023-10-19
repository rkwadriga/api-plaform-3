<?php declare(strict_types=1);
/**
 * Created 2023-10-19 19:07:26
 * Author rkwadriga
 */

namespace App\Security;

use App\Repository\ApiTokenRepository;
use SensitiveParameter;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class ApiTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        private readonly ApiTokenRepository $tokenRepository
    ) {
    }

    public function getUserBadgeFrom(#[SensitiveParameter] string $accessToken): UserBadge
    {
        $token = $this->tokenRepository->findOneBy(['token' => $accessToken]);
        if ($token === null) {
            throw new BadCredentialsException('Invalid credentials.');
        }

        if (!$token->isValid()) {
            throw new CustomUserMessageAuthenticationException('Token expired.');
        }

        $token->getOwnedBy()->markAsTokenAuthenticated($token->getScopes());

        return new UserBadge($token->getOwnedBy()->getUserIdentifier(), fn () => $token->getOwnedBy());
    }
}