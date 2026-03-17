<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use App\Repository\AuthorRepository;

#[ORM\Entity(repositoryClass: AuthorRepository::class)]
#[ORM\Table(name: 'author')]
class Author
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['author:list', 'author:read', 'book:list', 'book:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['author:list', 'author:read', 'book:list', 'book:read'])]
    private ?string $lastName = null;

    #[ORM\Column(length: 100)]
    #[Groups(['author:list', 'author:read', 'book:list', 'book:read'])]
    private ?string $firstName = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['author:read'])]
    private ?\DateTime $birthDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['author:read'])]
    private ?\DateTime $deathDate = null;

    #[ORM\Column(length: 50)]
    #[Groups(['author:list', 'author:read'])]
    private ?string $nationality = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['author:read'])]
    private ?string $photo = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['author:read'])]
    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDeathDate(): ?\DateTime
    {
        return $this->deathDate;
    }

    public function setDeathDate(?\DateTime $deathDate): static
    {
        $this->deathDate = $deathDate;

        return $this;
    }

    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    public function setNationality(string $nationality): static
    {
        $this->nationality = $nationality;

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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function __toString(): string
    {
        return ($this->lastName ?? '/') . ' ' . ($this->firstName ?? '/');
    }
}

