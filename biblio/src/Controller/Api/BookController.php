<?php

namespace App\Controller\Api;

use App\Entity\Book;
use App\Repository\BookRepository;
use App\Repository\LoanRepository;
use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class BookController extends AbstractController
{
    #[Route('/books', name: 'api_books', methods: ['GET'])]
    public function index(Request $request, BookRepository $bookRepository): JsonResponse
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = min(100, max(1, $request->query->getInt('limit', 20)));

        $books = $bookRepository->findPaginated($page, $limit);
        $total = $bookRepository->countAll();

        return $this->json([
            'data' => $books,
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'totalPages' => (int) ceil($total / $limit),
            ],
        ], 200, [], ['groups' => 'book:list']);
    }

    #[Route('/books/{id}/reservation-status', name: 'api_books_reservation_status', methods: ['GET'])]
    public function reservationStatus(Book $book, ReservationRepository $reservationRepository, LoanRepository $loanRepository): JsonResponse
    {
        $reservationRepository->deleteExpired();

        $existing = $reservationRepository->findOneByBookId($book->getId());
        if ($existing) {
            return $this->json(['reservable' => false, 'reason' => 'reserved']);
        }

        $activeLoan = $loanRepository->findActiveByBookId($book->getId());
        if ($activeLoan) {
            return $this->json(['reservable' => false, 'reason' => 'loaned']);
        }

        return $this->json(['reservable' => true]);
    }

    #[Route('/books/{id}', name: 'api_books_show', methods: ['GET'])]
    public function show(Book $book): JsonResponse
    {
        return $this->json($book, 200, [], ['groups' => 'book:read']);
    }

    #[Route('/books/search', name: 'api_books_search', methods: ['GET'], priority: 10)]
    public function search(Request $request, BookRepository $bookRepository): JsonResponse
    {
        $title = $request->query->get('title');
        $authorId = $request->query->get('author') !== null ? $request->query->getInt('author') : null;
        $categoryIds = null;
        $categoriesParam = $request->query->get('categories');
        if ($categoriesParam !== null && $categoriesParam !== '') {
            $parsed = [];
            foreach (explode(',', (string) $categoriesParam) as $part) {
                $id = (int) trim($part);
                if ($id > 0) {
                    $parsed[] = $id;
                }
            }
            $parsed = array_slice(array_unique($parsed), 0, 3);
            $categoryIds = $parsed !== [] ? $parsed : null;
        } elseif ($request->query->get('category') !== null) {
            $id = $request->query->getInt('category');
            $categoryIds = $id > 0 ? [$id] : null;
        }
        $language = $request->query->get('language');
        $yearFrom = $request->query->get('yearFrom') !== null ? $request->query->getInt('yearFrom') : null;
        $yearTo = $request->query->get('yearTo') !== null ? $request->query->getInt('yearTo') : null;

        $page = max(1, $request->query->getInt('page', 1));
        $limit = min(100, max(1, $request->query->getInt('limit', 20)));

        $qb = $bookRepository->createSearchQueryBuilder($title, $authorId, $categoryIds, $language, $yearFrom, $yearTo);

        $total = (int) (clone $qb)->select('COUNT(DISTINCT b.id)')->getQuery()->getSingleScalarResult();

        $idsQb = clone $qb;
        $idsQb->resetDQLPart('select');
        $idsQb->select('b.id')->distinct(true)
            ->orderBy('b.title', 'ASC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
        $idRows = $idsQb->getQuery()->getScalarResult();
        $ids = array_map('intval', array_column($idRows, 'id'));
        $books = $bookRepository->findByIdsWithAuthorAndCategories($ids);

        return $this->json([
            'data' => $books,
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'totalPages' => (int) ceil($total / $limit),
            ],
        ], 200, [], ['groups' => 'book:list']);
    }
}
