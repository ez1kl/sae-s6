<?php

namespace App\Controller\Api;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class CategoryController extends AbstractController
{
    #[Route('/categories', name: 'api_categories', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository): JsonResponse
    {
        $categories = $categoryRepository->findAll();

        return $this->json($categories, 200, [], ['groups' => 'category:read']);
    }

    #[Route('/categories/{id}', name: 'api_categories_show', methods: ['GET'])]
    public function show(Category $category): JsonResponse
    {
        return $this->json($category, 200, [], ['groups' => 'category:read']);
    }
}
