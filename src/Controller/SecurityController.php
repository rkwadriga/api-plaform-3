<?php declare(strict_types=1);

namespace App\Controller;

use ApiPlatform\Api\IriConverterInterface;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class SecurityController extends AbstractController
{
    #[Route('/api/login', name: 'app_login', methods: [Request::METHOD_POST])]
    public function login(IriConverterInterface $iriConverter, #[CurrentUser] User $user = null): Response
    {
        if ($user === null) {
            return $this->json([
                'error' => 'Invalid request: check that the Content-Type header is "application/json"'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return new Response(null, Response::HTTP_NO_CONTENT, [
            'Location' => $iriConverter->getIriFromResource($user),
        ]);
    }
}
