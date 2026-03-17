<?php

namespace App\Entity;

use App\Repository\MemberRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MemberRepository::class)]
#[ORM\Table(name: 'member', uniqueConstraints: [
    new ORM\UniqueConstraint(name: 'uniq_member_user', columns: ['user_id']),
])]
class Member
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['member:read'])]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['member:read'])]
    #[Assert\NotNull(message: "La date d'adhésion est obligatoire.")]
    private ?\DateTime $membershipDate = null;

    #[ORM\Column(length: 100)]
    #[Groups(['member:read'])]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(max: 100, maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $lastName = null;

    #[ORM\Column(length: 100)]
    #[Groups(['member:read'])]
    #[Assert\NotBlank(message: 'Le prénom est obligatoire.')]
    #[Assert\Length(max: 100, maxMessage: 'Le prénom ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $firstName = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['member:read'])]
    #[Assert\NotNull(message: 'La date de naissance est obligatoire.')]
    private ?\DateTime $birthDate = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['member:read'])]
    #[Assert\Length(max: 20, maxMessage: 'Le numéro de téléphone ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $phoneNumber = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['member:read'])]
    private ?string $address = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['member:read'])]
    private ?string $photo = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    #[Groups(['member:read'])]
    private bool $suspended = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getMembershipDate(): ?\DateTime
    {
        return $this->membershipDate;
    }

    public function setMembershipDate(\DateTime $membershipDate): static
    {
        $this->membershipDate = $membershipDate;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getBirthDate(): ?\DateTime
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTime $birthDate): static
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function isSuspended(): bool
    {
        return $this->suspended;
    }

    public function setSuspended(bool $suspended): static
    {
        $this->suspended = $suspended;

        return $this;
    }

    public function __toString(): string
    {
        return ($this->lastName ?? '/') . ' ' . ($this->firstName ?? '/');
    }
}
