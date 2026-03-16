<?php

namespace App\Controller\Api;

use App\Repository\BookRepository;
use App\Repository\LoanRepository;
use App\Repository\MemberRepository;
use App\Repository\ReservationRepository;
use App\Service\LoanService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/librarian')]
#[IsGranted('ROLE_BIBLIOTHECAIRE')]
class LibrarianController extends AbstractController
{
    #[Route('/members/search', name: 'api_librarian_members_search', methods: ['GET'])]
    public function searchMembers(Request $request, MemberRepository $memberRepository): JsonResponse
    {
        $query = $request->query->get('q', '');

        if (strlen($query) < 2) {
            return $this->json([]);
        }

        $members = $memberRepository->searchByName($query);

        $data = array_map(fn($m) => [
            'id' => $m->getId(),
            'firstName' => $m->getFirstName(),
            'lastName' => $m->getLastName(),
            'email' => $m->getUser()?->getEmail(),
            'suspended' => $m->isSuspended(),
        ], $members);

        return $this->json($data);
    }

    #[Route('/members/{id}', name: 'api_librarian_members_show', methods: ['GET'])]
    public function memberProfile(
        int $id,
        MemberRepository $memberRepository,
        LoanRepository $loanRepository,
        ReservationRepository $reservationRepository,
        LoanService $loanService,
    ): JsonResponse {
        $member = $memberRepository->find($id);
        if (!$member) {
            return $this->json(['error' => 'Adhérent introuvable.'], 404);
        }

        $activeLoans = $loanRepository->findActiveLoansByMember($member);
        $reservations = $reservationRepository->findByMemberId($id);
        $now = new \DateTime();

        return $this->json([
            'member' => [
                'id' => $member->getId(),
                'firstName' => $member->getFirstName(),
                'lastName' => $member->getLastName(),
                'email' => $member->getUser()?->getEmail(),
                'suspended' => $member->isSuspended(),
                'activeLoansCount' => count($activeLoans),
                'maxLoans' => $loanService->getMaxLoanQuota(),
            ],
            'activeLoans' => array_map(fn($l) => [
                'id' => $l->getId(),
                'book' => ['id' => $l->getBook()->getId(), 'title' => $l->getBook()->getTitle()],
                'loanDate' => $l->getLoanDate()->format('Y-m-d'),
                'dueDate' => $l->getDueDate()->format('Y-m-d'),
                'isOverdue' => $l->getDueDate() < $now,
                'daysOverdue' => $l->getDueDate() < $now ? $l->getDueDate()->diff($now)->days : 0,
            ], $activeLoans),
            'reservations' => array_map(fn($r) => [
                'id' => $r->getId(),
                'book' => ['id' => $r->getBook()->getId(), 'title' => $r->getBook()->getTitle()],
                'createdAt' => $r->getCreatedAt()->format('Y-m-d H:i'),
            ], $reservations),
        ]);
    }

    #[Route('/loans', name: 'api_librarian_loans_create', methods: ['POST'])]
    public function createLoan(
        Request $request,
        MemberRepository $memberRepository,
        BookRepository $bookRepository,
        LoanService $loanService,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['memberId'], $data['bookId'])) {
            return $this->json(['error' => 'Les champs memberId et bookId sont requis.'], 400);
        }

        $member = $memberRepository->find($data['memberId']);
        if (!$member) {
            return $this->json(['error' => 'Adhérent introuvable.'], 404);
        }

        $book = $bookRepository->find($data['bookId']);
        if (!$book) {
            return $this->json(['error' => 'Livre introuvable.'], 404);
        }

        $force = $data['force'] ?? false;
        $check = $loanService->canLendBookToMember($book, $member);

        if (!$check['allowed']) {
            return $this->json(['success' => false, 'error' => $check['reason']], 422);
        }

        if (!empty($check['warning']) && !$force) {
            return $this->json([
                'success' => false,
                'warning' => $check['warning'],
                'requireConfirmation' => true,
            ], 409);
        }

        $loan = $loanService->registerLoan($book, $member);

        return $this->json([
            'success' => true,
            'loan' => [
                'id' => $loan->getId(),
                'book' => ['id' => $book->getId(), 'title' => $book->getTitle()],
                'member' => ['id' => $member->getId(), 'firstName' => $member->getFirstName(), 'lastName' => $member->getLastName()],
                'loanDate' => $loan->getLoanDate()->format('Y-m-d'),
                'dueDate' => $loan->getDueDate()->format('Y-m-d'),
            ],
        ], 201);
    }

    #[Route('/loans/{id}/return', name: 'api_librarian_loans_return', methods: ['PUT'])]
    public function returnLoan(
        int $id,
        LoanRepository $loanRepository,
        LoanService $loanService,
    ): JsonResponse {
        $loan = $loanRepository->find($id);
        if (!$loan) {
            return $this->json(['error' => 'Emprunt introuvable.'], 404);
        }

        if ($loan->getReturnDate() !== null) {
            return $this->json(['error' => 'Ce livre a déjà été retourné.'], 422);
        }

        $loan = $loanService->registerReturn($loan);

        return $this->json([
            'id' => $loan->getId(),
            'book' => ['id' => $loan->getBook()->getId(), 'title' => $loan->getBook()->getTitle()],
            'loanDate' => $loan->getLoanDate()->format('Y-m-d'),
            'dueDate' => $loan->getDueDate()->format('Y-m-d'),
            'returnDate' => $loan->getReturnDate()->format('Y-m-d'),
        ]);
    }

    #[Route('/active-loans', name: 'api_librarian_active_loans', methods: ['GET'])]
    public function activeLoans(LoanRepository $loanRepository): JsonResponse
    {
        $loans = $loanRepository->findActiveLoans();
        $now = new \DateTime();

        $data = array_map(fn($l) => [
            'id' => $l->getId(),
            'book' => ['id' => $l->getBook()->getId(), 'title' => $l->getBook()->getTitle()],
            'member' => [
                'id' => $l->getMember()->getId(),
                'firstName' => $l->getMember()->getFirstName(),
                'lastName' => $l->getMember()->getLastName(),
            ],
            'loanDate' => $l->getLoanDate()->format('Y-m-d'),
            'dueDate' => $l->getDueDate()->format('Y-m-d'),
            'isOverdue' => $l->getDueDate() < $now,
            'daysOverdue' => $l->getDueDate() < $now ? $l->getDueDate()->diff($now)->days : 0,
        ], $loans);

        return $this->json($data);
    }

    #[Route('/overdue-loans', name: 'api_librarian_overdue_loans', methods: ['GET'])]
    public function overdueLoans(LoanRepository $loanRepository): JsonResponse
    {
        $loans = $loanRepository->findOverdueLoans();
        $now = new \DateTime();

        $data = array_map(fn($l) => [
            'id' => $l->getId(),
            'book' => ['id' => $l->getBook()->getId(), 'title' => $l->getBook()->getTitle()],
            'member' => [
                'id' => $l->getMember()->getId(),
                'firstName' => $l->getMember()->getFirstName(),
                'lastName' => $l->getMember()->getLastName(),
            ],
            'loanDate' => $l->getLoanDate()->format('Y-m-d'),
            'dueDate' => $l->getDueDate()->format('Y-m-d'),
            'daysOverdue' => $l->getDueDate()->diff($now)->days,
        ], $loans);

        return $this->json($data);
    }
}
