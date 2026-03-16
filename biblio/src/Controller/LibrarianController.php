<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Member;
use App\Repository\LoanRepository;
use App\Service\LoanService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/librarian', name: 'librarian_')]
class LibrarianController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private LoanService $loanService,
        private LoanRepository $loanRepository,
    ) {}

    #[Route('', name: 'dashboard')]
    public function dashboard(Request $request): Response
    {
        $search = $request->query->get('q', '');
        $type = $request->query->get('type', 'title');

        if (!empty($search)) {
            $activeLoans = match ($type) {
                'author' => $this->loanRepository->searchActiveLoansByAuthor($search),
                'member' => $this->loanRepository->searchActiveLoansByMember($search),
                default  => $this->loanRepository->searchActiveLoans($search),
            };
            $overdueLoans = [];
        } else {
            $activeLoans = $this->loanRepository->findActiveLoans();
            $overdueLoans = $this->loanRepository->findOverdueLoans();
        }

        $totalBooks = $this->em->getRepository(Book::class)->count([]);
        $totalMembers = $this->em->getRepository(Member::class)->count([]);
        $allActiveCount = $this->loanRepository->countActiveLoans();
        $availableBooks = $totalBooks - $allActiveCount;

        return $this->render('librarian/dashboard.html.twig', [
            'activeLoans' => $activeLoans,
            'overdueLoans' => $overdueLoans,
            'totalMembers' => $totalMembers,
            'availableBooks' => $availableBooks,
            'searchQuery' => $search,
            'searchType' => $type,
        ]);
    }

    #[Route('/loan', name: 'express_loan')]
    public function expressLoan(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $bookId = $request->request->get('book_id');
            $memberId = $request->request->get('member_id');

            try {
                $book = $this->em->getRepository(Book::class)->find($bookId);
                $member = $this->em->getRepository(Member::class)->find($memberId);

                if (!$book || !$member) {
                    $this->addFlash('danger', 'Livre ou membre introuvable.');
                } else {
                    $check = $this->loanService->canLendBookToMember($book, $member);
                    if (!$check['allowed']) {
                        $this->addFlash('danger', $check['reason']);
                    } else {
                        $this->loanService->registerLoan($book, $member);
                        $this->addFlash('success', sprintf('Prêt enregistré pour "%s" (Membre: %s)', $book->getTitle(), $member->getLastName()));
                        return $this->redirectToRoute('librarian_dashboard');
                    }
                }
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
            }
        }

        $allBooks = $this->em->getRepository(Book::class)->findAll();
        $members = $this->em->getRepository(Member::class)->findAll();

        return $this->render('librarian/express_loan.html.twig', [
            'books' => $allBooks,
            'members' => $members,
        ]);
    }

    #[Route('/return', name: 'express_return')]
    public function expressReturn(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $bookId = $request->request->get('book_id');

            try {
                $book = $this->em->getRepository(Book::class)->find($bookId);
                if (!$book) {
                    $this->addFlash('danger', 'Livre introuvable.');
                } else {
                    $loan = $this->loanRepository->findActiveLoanByBook($book);
                    if (!$loan) {
                        $this->addFlash('danger', 'Aucun emprunt actif trouvé pour ce livre.');
                    } else {
                        $this->loanService->registerReturn($loan);
                        $this->addFlash('success', sprintf('Retour enregistré pour "%s"', $book->getTitle()));
                        return $this->redirectToRoute('librarian_dashboard');
                    }
                }
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
            }
        }

        $activeLoans = $this->loanRepository->findActiveLoans();

        return $this->render('librarian/express_return.html.twig', [
            'loans' => $activeLoans,
        ]);
    }
}
