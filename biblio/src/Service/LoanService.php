<?php

namespace App\Service;

use App\Domain\LibraryRules;
use App\Entity\Book;
use App\Entity\Loan;
use App\Entity\Member;
use App\Repository\LoanRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;

class LoanService
{
    public function __construct(
        private LoanRepository $loanRepository,
        private ReservationRepository $reservationRepository,
        private EntityManagerInterface $em,
    ) {}

    public function isBookAvailable(Book $book): bool
    {
        return $this->loanRepository->findActiveLoanByBook($book) === null;
    }

    public function canMemberBorrow(Member $member): array
    {
        if ($member->isSuspended()) {
            return ['allowed' => false, 'reason' => 'L\'adhérent est suspendu.'];
        }

        $activeCount = $this->loanRepository->countActiveByMember($member);
        if ($activeCount >= LibraryRules::MAX_ACTIVE_LOANS) {
            $max = LibraryRules::MAX_ACTIVE_LOANS;
            return ['allowed' => false, 'reason' => "Quota maximum atteint ({$max} emprunts)."];
        }

        return ['allowed' => true, 'reason' => null];
    }

    public function canLendBookToMember(Book $book, Member $member): array
    {
        $memberCheck = $this->canMemberBorrow($member);
        if (!$memberCheck['allowed']) {
            return $memberCheck;
        }

        if (!$this->isBookAvailable($book)) {
            return ['allowed' => false, 'reason' => 'Ce livre est déjà emprunté.', 'warning' => null];
        }

        $reservation = $this->reservationRepository->findOneByBookId($book->getId());
        $warning = null;
        if ($reservation !== null && $reservation->getMember()->getId() !== $member->getId()) {
            $reservedBy = $reservation->getMember();
            $warning = "Ce livre est réservé par {$reservedBy->getFirstName()} {$reservedBy->getLastName()}.";
        }

        return ['allowed' => true, 'reason' => null, 'warning' => $warning];
    }

    public function registerLoan(Book $book, Member $member): Loan
    {
        $loan = new Loan();
        $loan->setBook($book);
        $loan->setMember($member);

        // Remove reservation for this book if it exists (by this member or force override)
        $reservation = $this->reservationRepository->findOneByBookId($book->getId());
        if ($reservation !== null) {
            $this->em->remove($reservation);
        }

        $this->em->persist($loan);
        $this->em->flush();

        return $loan;
    }

    public function registerReturn(Loan $loan): Loan
    {
        $loan->setReturnDate(new \DateTime());
        $this->em->flush();

        return $loan;
    }

    public function getMaxLoanQuota(): int
    {
        return LibraryRules::MAX_ACTIVE_LOANS;
    }
}
