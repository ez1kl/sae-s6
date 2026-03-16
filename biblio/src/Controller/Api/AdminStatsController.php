<?php

namespace App\Controller\Api;

use App\Repository\BookRepository;
use App\Repository\LoanRepository;
use App\Repository\MemberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin/stats')]
#[IsGranted('ROLE_RESPONSABLE')]
class AdminStatsController extends AbstractController
{
    #[Route('/overview', name: 'api_admin_stats_overview', methods: ['GET'])]
    public function overview(
        BookRepository $bookRepository,
        MemberRepository $memberRepository,
        LoanRepository $loanRepository,
    ): JsonResponse {
        return $this->json([
            'totalBooks' => $bookRepository->countAll(),
            'activeMembers' => $memberRepository->countActive(),
            'currentLoans' => $loanRepository->countActiveLoans(),
            'overdueCount' => $loanRepository->countOverdueLoans(),
        ]);
    }

    #[Route('/loans-by-month', name: 'api_admin_stats_loans_by_month', methods: ['GET'])]
    public function loansByMonth(Request $request, LoanRepository $loanRepository): JsonResponse
    {
        $months = $request->query->getInt('months', 12);
        $stats = $loanRepository->getMonthlyLoanStats($months);

        return $this->json($stats);
    }

    #[Route('/loans-by-category', name: 'api_admin_stats_loans_by_category', methods: ['GET'])]
    public function loansByCategory(LoanRepository $loanRepository): JsonResponse
    {
        $stats = $loanRepository->getLoansByCategory();

        return $this->json($stats);
    }

    #[Route('/overdue', name: 'api_admin_stats_overdue', methods: ['GET'])]
    public function overdue(LoanRepository $loanRepository): JsonResponse
    {
        $loans = $loanRepository->findOverdueLoans();
        $now = new \DateTime();

        $result = array_map(function ($loan) use ($now) {
            $daysOverdue = $loan->getDueDate()->diff($now)->days;

            return [
                'id' => $loan->getId(),
                'book' => [
                    'id' => $loan->getBook()->getId(),
                    'title' => $loan->getBook()->getTitle(),
                ],
                'member' => [
                    'id' => $loan->getMember()->getId(),
                    'firstName' => $loan->getMember()->getFirstName(),
                    'lastName' => $loan->getMember()->getLastName(),
                ],
                'loanDate' => $loan->getLoanDate()->format('Y-m-d'),
                'dueDate' => $loan->getDueDate()->format('Y-m-d'),
                'daysOverdue' => $daysOverdue,
            ];
        }, $loans);

        return $this->json($result);
    }
}
