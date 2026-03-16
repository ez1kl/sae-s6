<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Loan;
use App\Entity\Member;
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
        private LoanService $loanService
    ) {}

    #[Route('', name: 'dashboard')]
    public function dashboard(Request $request): Response
    {
        $search = $request->query->get('q', '');
        
        if (!empty($search)) {
            $activeLoans = $this->em->getRepository(Loan::class)->searchActiveLoans($search);
            $overdueLoans = []; // Filter this if needed
        } else {
            $activeLoans = $this->em->getRepository(Loan::class)->findActiveLoans();
            $overdueLoans = $this->em->getRepository(Loan::class)->findOverdueLoans();
        }

        $totalBooks = $this->em->getRepository(Book::class)->count([]);
        $totalMembers = $this->em->getRepository(Member::class)->count([]);
        $allActiveCount = $this->em->getRepository(Loan::class)->countActiveLoans();
        $availableBooks = $totalBooks - $allActiveCount;

        return $this->render('librarian/dashboard.html.twig', [
            'activeLoans' => $activeLoans,
            'overdueLoans' => $overdueLoans,
            'totalMembers' => $totalMembers,
            'availableBooks' => $availableBooks,
            'searchQuery' => $search
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
                    $this->loanService->createLoan($member, $book);
                    $this->addFlash('success', sprintf('Prêt enregistré pour "%s" (Membre: %s)', $book->getTitle(), $member->getLastName()));
                    return $this->redirectToRoute('librarian_dashboard');
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
                    $this->loanService->returnBook($book);
                    $this->addFlash('success', sprintf('Retour enregistré pour "%s"', $book->getTitle()));
                    return $this->redirectToRoute('librarian_dashboard');
                }
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
            }
        }

        $activeLoans = $this->em->getRepository(Loan::class)->findActiveLoans();

        return $this->render('librarian/express_return.html.twig', [
            'loans' => $activeLoans,
        ]);
    }
}
