<?php

namespace App\Controller\Admin;

use App\Entity\Loan;
use App\Entity\Book;
use App\Entity\Member;
use App\Service\LoanService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[AdminDashboard(routePath: '/admin/librarian', routeName: 'admin_librarian_dashboard')]
class LibrarianDashboardController extends AbstractDashboardController
{
    public function __construct(
        private EntityManagerInterface $em,
        private LoanService $loanService
    ) {}

    public function index(): Response
    {
        $activeLoans = $this->em->getRepository(Loan::class)->findActiveLoans();
        $overdueLoans = $this->em->getRepository(Loan::class)->findOverdueLoans();
        $totalBooks = $this->em->getRepository(Book::class)->count([]);
        $totalMembers = $this->em->getRepository(Member::class)->count([]);
        $activeLoansCount = count($activeLoans);
        $availableBooks = $totalBooks - $activeLoansCount;

        return $this->render('Admin/librarian_dashboard.html.twig', [
            'activeLoans' => $activeLoans,
            'overdueLoans' => $overdueLoans,
            'totalMembers' => $totalMembers,
            'availableBooks' => $availableBooks,
        ]);
    }

    #[Route('/admin/librarian/express-loan', name: 'admin_librarian_express_loan', methods: ['GET', 'POST'])]
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
                    return $this->redirectToRoute('admin_librarian_dashboard');
                }
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
            }
        }

        $allBooks = $this->em->getRepository(Book::class)->findAll();
        $members = $this->em->getRepository(Member::class)->findAll();

        return $this->render('Admin/express_loan.html.twig', [
            'books' => $allBooks,
            'members' => $members,
        ]);
    }

    #[Route('/admin/librarian/express-return', name: 'admin_librarian_express_return', methods: ['GET', 'POST'])]
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
                    return $this->redirectToRoute('admin_librarian_dashboard');
                }
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Erreur : ' . $e->getMessage());
            }
        }

        $activeLoans = $this->em->getRepository(Loan::class)->findActiveLoans();

        return $this->render('Admin/express_return.html.twig', [
            'loans' => $activeLoans,
        ]);
    }
}
