<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login', methods: [Request::METHOD_POST])]
    public function login(#[CurrentUser] User $user = null): Response
    {
        return $this->json([
            'user' => $user?->getId(),
        ]);
    }
}
