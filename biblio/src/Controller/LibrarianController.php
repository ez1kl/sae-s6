<?php

namespace App\Controller;

use App\Domain\LibraryRules;
use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Member;
use App\Entity\User;
use App\Repository\LoanRepository;
use App\Repository\MemberRepository;
use App\Repository\ReservationRepository;
use App\Service\LoanService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/librarian', name: 'librarian_')]
class LibrarianController extends AbstractController
{
    private const MEMBER_PAGE_SIZE = 10;
    private const MEMBER_RESERVATION_LIMIT = LibraryRules::MAX_ACTIVE_RESERVATIONS;

    public function __construct(
        private EntityManagerInterface $em,
        private LoanService $loanService,
        private LoanRepository $loanRepository,
        private MemberRepository $memberRepository,
        private ReservationRepository $reservationRepository,
        private UserPasswordHasherInterface $passwordHasher,
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
        $totalAuthors = $this->em->getRepository(Author::class)->count([]);
        $allActiveCount = $this->loanRepository->countActiveLoans();
        $availableBooks = $totalBooks - $allActiveCount;

        return $this->render('librarian/dashboard.html.twig', [
            'activeLoans' => $activeLoans,
            'overdueLoans' => $overdueLoans,
            'totalBooks' => $totalBooks,
            'totalMembers' => $totalMembers,
            'totalAuthors' => $totalAuthors,
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
            $force = $request->request->getBoolean('force', false);

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
                        if (($check['warning'] ?? null) !== null && !$force) {
                            return $this->render('librarian/express_loan.html.twig', [
                                'books' => $this->em->getRepository(Book::class)->findAll(),
                                'members' => $this->em->getRepository(Member::class)->findAll(),
                                'reservationWarning' => (string) $check['warning'],
                                'pendingBook' => $book,
                                'pendingMemberId' => $member->getId(),
                            ]);
                        }

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

    #[Route('/members', name: 'members', methods: ['GET'])]
    public function members(Request $request): Response
    {
        $search = trim((string) $request->query->get('q', ''));
        $page = max(1, $request->query->getInt('page', 1));

        $totalMembers = $this->memberRepository->countBySearch($search);
        $totalPages = max(1, (int) ceil($totalMembers / self::MEMBER_PAGE_SIZE));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $memberRows = $this->memberRepository->findPaginatedWithStats(
            $search,
            $page,
            self::MEMBER_PAGE_SIZE,
            new \DateTimeImmutable(),
        );

        return $this->render('librarian/members.html.twig', [
            'memberRows' => $memberRows,
            'searchQuery' => $search,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalMembers' => $totalMembers,
            'reservationLimit' => self::MEMBER_RESERVATION_LIMIT,
        ]);
    }

    #[Route('/members/suggest', name: 'members_suggest', methods: ['GET'])]
    public function memberSuggestions(Request $request): JsonResponse
    {
        $query = trim((string) $request->query->get('q', ''));

        if (mb_strlen($query) < 2) {
            return $this->json(['suggestions' => []]);
        }

        return $this->json([
            'suggestions' => $this->memberRepository->searchSuggestions($query),
        ]);
    }

    #[Route('/members/{id}', name: 'member_detail', methods: ['GET'])]
    public function memberDetail(int $id): Response
    {
        $member = $this->memberRepository->find($id);

        if (!$member) {
            throw $this->createNotFoundException('Membre introuvable.');
        }

        $activeLoans = $this->loanRepository->findActiveLoansByMember($member);
        $loanHistory = $this->loanRepository->findCompletedLoansByMember($member);
        $reservations = $this->reservationRepository->findByMember($member);

        $now = new \DateTimeImmutable();
        $overdueCount = 0;
        foreach ($activeLoans as $loan) {
            if ($loan->getDueDate() < $now) {
                ++$overdueCount;
            }
        }

        return $this->render('librarian/member_detail.html.twig', [
            'member' => $member,
            'activeLoans' => $activeLoans,
            'loanHistory' => $loanHistory,
            'reservations' => $reservations,
            'overdueCount' => $overdueCount,
            'reservationLimit' => self::MEMBER_RESERVATION_LIMIT,
        ]);
    }

    #[Route('/members/{id}/suspend', name: 'member_suspend', methods: ['POST'])]
    public function toggleMemberSuspension(Request $request, int $id): Response
    {
        $member = $this->memberRepository->find($id);

        if (!$member) {
            throw $this->createNotFoundException('Membre introuvable.');
        }

        if (!$this->isCsrfTokenValid('suspend_member_' . $member->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');

            return $this->redirectToRoute('librarian_member_detail', ['id' => $member->getId()]);
        }

        $member->setSuspended(!$member->isSuspended());
        $this->em->flush();

        $this->addFlash(
            'success',
            $member->isSuspended()
                ? 'Les réservations de ce membre sont désormais suspendues.'
                : 'Les réservations de ce membre sont à nouveau autorisées.'
        );

        return $this->redirectToRoute('librarian_member_detail', ['id' => $member->getId()]);
    }

    #[Route('/members/create', name: 'member_create', methods: ['POST'])]
    public function createMember(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('create_member', (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');

            return $this->redirectToRoute('librarian_members');
        }

        $firstName = trim((string) $request->request->get('first_name', ''));
        $lastName = trim((string) $request->request->get('last_name', ''));
        $email = mb_strtolower(trim((string) $request->request->get('email', '')));
        $birthDateInput = trim((string) $request->request->get('birth_date', ''));
        $membershipDateInput = trim((string) $request->request->get('membership_date', ''));
        $phoneNumber = trim((string) $request->request->get('phone_number', ''));
        $address = trim((string) $request->request->get('address', ''));

        if ($firstName === '' || $lastName === '' || $email === '' || $birthDateInput === '') {
            $this->addFlash('danger', 'Nom, prénom, email et date de naissance sont obligatoires.');

            return $this->redirectToRoute('librarian_members');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addFlash('danger', 'Adresse email invalide.');

            return $this->redirectToRoute('librarian_members');
        }

        $existingUser = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $this->addFlash('danger', 'Un compte existe déjà avec cet email.');

            return $this->redirectToRoute('librarian_members');
        }

        $birthDate = \DateTime::createFromFormat('Y-m-d', $birthDateInput);
        if (!$birthDate) {
            $this->addFlash('danger', 'Date de naissance invalide.');

            return $this->redirectToRoute('librarian_members');
        }

        $membershipDate = null;
        if ($membershipDateInput !== '') {
            $membershipDate = \DateTime::createFromFormat('Y-m-d', $membershipDateInput);
            if (!$membershipDate) {
                $this->addFlash('danger', 'Date d\'adhésion invalide.');

                return $this->redirectToRoute('librarian_members');
            }
        }

        $rawPassword = bin2hex(random_bytes(5));

        $user = new User();
        $user->setEmail($email);
        $user->setRoles(['ROLE_MEMBRE']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $rawPassword));

        $member = new Member();
        $member->setUser($user);
        $member->setFirstName($firstName);
        $member->setLastName($lastName);
        $member->setBirthDate($birthDate);
        $member->setMembershipDate($membershipDate ?? new \DateTime());
        $member->setPhoneNumber($phoneNumber !== '' ? $phoneNumber : null);
        $member->setAddress($address !== '' ? $address : '');

        $this->em->persist($user);
        $this->em->persist($member);
        $this->em->flush();

        $message = sprintf('Membre inscrit avec succès. Mot de passe temporaire: %s', $rawPassword);
        $this->addFlash('success', $message);

        return $this->redirectToRoute('librarian_member_detail', ['id' => $member->getId()]);
    }
}
