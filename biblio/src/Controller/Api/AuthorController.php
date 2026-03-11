<?php

namespace App\Controller\Api;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class AuthorController extends AbstractController
{
    #[Route('/authors', name: 'api_authors', methods: ['GET'])]
    public function index(AuthorRepository $authorRepository): JsonResponse
    {
        $authors = $authorRepository->findAll();

        return $this->json($authors, 200, [], ['groups' => 'author:list']);
    }

    #[Route('/authors/{id}', name: 'api_authors_show', methods: ['GET'])]
    public function show(Author $author): JsonResponse
    {
        return $this->json($author, 200, [], ['groups' => 'author:read']);
    }
}
