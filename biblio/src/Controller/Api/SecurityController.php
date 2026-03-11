<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class SecurityController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        // This method is intercepted by the JWT authenticator.
        // If we reach here, authentication failed.
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'Identifiants invalides.'], 401);
        }

        // This will never be reached when JWT authenticator is active,
        // but is here as a fallback.
        return $this->json(['message' => 'Authentifié.']);
    }
}
