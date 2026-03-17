<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\CategoryRepository;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[ORM\Table(name: 'category')]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['category:read', 'book:list', 'book:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['category:read', 'book:list', 'book:read'])]
    #[Assert\NotBlank(message: 'Le nom de la catégorie est obligatoire.')]
    #[Assert\Length(max: 100, maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['category:read'])]
    #[Assert\NotBlank(message: 'La description est obligatoire.')]
    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '/';
    }
}
