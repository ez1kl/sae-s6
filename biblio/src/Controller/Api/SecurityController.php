<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class SecurityController extends AbstractController
{
    #[Route('/login', name: 'api_login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        // Route gérée par le firewall json_login
        return $this->json(['message' => 'Missing credentials'], 401);
    }

    #[Route('/user/me', name: 'api_user_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        /** @var Utilisateur|null $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['message' => 'Not authenticated'], 401);
        }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'member' => $user->getMember() ? [
                'id' => $user->getMember()->getId(),
                'last_name' => $user->getMember()->getLastName(),
                'first_name' => $user->getMember()->getFirstName(),
            ] : null,
            'roles' => $user->getRoles(),
        ]);
    }
}
