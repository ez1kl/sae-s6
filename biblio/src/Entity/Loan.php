<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use App\Repository\LoanRepository;

#[ORM\Entity(repositoryClass: LoanRepository::class)]
#[ORM\Table(name: 'loan', indexes: [
    new ORM\Index(name: 'idx_loan_book', columns: ['book_id']),
    new ORM\Index(name: 'idx_loan_member', columns: ['member_id']),
])]
class Loan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['loan:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Book::class)]
    #[ORM\JoinColumn(name: 'book_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT')]
    #[Groups(['loan:read'])]
    private ?Book $book = null;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'member_id', referencedColumnName: 'id', nullable: false, onDelete: 'RESTRICT')]
    private ?Member $member = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['loan:read'])]
    private ?\DateTime $loanDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['loan:read'])]
    private ?\DateTime $dueDate = null;

    public function __construct()
    {
        $now = new \DateTime();
        $this->loanDate = $now;
        $this->dueDate = (clone $now)->modify('+15 days');
    }

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['loan:read'])]
    private ?\DateTime $returnDate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBook(): ?Book
    {
        return $this->book;
    }

    public function setBook(Book $book): static
    {
        $this->book = $book;

        return $this;
    }

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function setMember(Member $member): static
    {
        $this->member = $member;

        return $this;
    }

    public function getLoanDate(): ?\DateTime
    {
        return $this->loanDate;
    }

    public function setLoanDate(\DateTime $loanDate): static
    {
        $this->loanDate = $loanDate;

        return $this;
    }

    public function getDueDate(): ?\DateTime
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTime $dueDate): static
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getReturnDate(): ?\DateTime
    {
        return $this->returnDate;
    }

    public function setReturnDate(?\DateTime $returnDate): static
    {
        $this->returnDate = $returnDate;

        return $this;
    }

    public function __toString(): string
    {
        $title = $this->book?->getTitle() ?? '/';
        $date = $this->loanDate?->format('d/m/Y') ?? '/';
        return sprintf('%s (%s)', $title, $date);
    }
}

