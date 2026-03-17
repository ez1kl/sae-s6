<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\BookRepository;

#[ORM\Entity(repositoryClass: BookRepository::class)]
#[ORM\Table(name: 'book', indexes: [
    new ORM\Index(name: 'idx_book_author', columns: ['author_id']),
])]
class Book
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['book:list', 'book:read', 'loan:read', 'reservation:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['book:list', 'book:read', 'loan:read', 'reservation:read'])]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(max: 255, maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $title = null;

    #[ORM\Column]
    #[Groups(['book:list', 'book:read'])]
    #[Assert\NotNull(message: "L'année de publication est obligatoire.")]
    #[Assert\Range(min: 1000, max: 2100, notInRangeMessage: "L'année doit être comprise entre {{ min }} et {{ max }}.")]
    private ?int $releaseYear = null;

    #[ORM\Column(length: 10)]
    #[Groups(['book:list', 'book:read'])]
    #[Assert\NotBlank(message: 'La langue est obligatoire.')]
    #[Assert\Length(max: 10)]
    private ?string $language = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['book:list', 'book:read'])]
    private ?string $coverImage = null;

    #[ORM\ManyToOne(targetEntity: Author::class)]
    #[ORM\JoinColumn(name: 'author_id', referencedColumnName: 'id', nullable: true, onDelete: 'RESTRICT')]
    #[Groups(['book:list', 'book:read'])]
    private ?Author $author = null;

    /**
     * @var Collection<int, Category>
     */
    #[ORM\ManyToMany(targetEntity: Category::class)]
    #[ORM\JoinTable(name: 'book_category')]
    #[ORM\JoinColumn(name: 'book_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[Groups(['book:list', 'book:read'])]
    private Collection $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getReleaseYear(): ?int
    {
        return $this->releaseYear;
    }

    public function setReleaseYear(int $releaseYear): static
    {
        $this->releaseYear = $releaseYear;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(string $language): static
    {
        $this->language = $language;

        return $this;
    }

    public function getCoverImage(): ?string
    {
        return $this->coverImage;
    }

    public function setCoverImage(?string $coverImage): static
    {
        $this->coverImage = $coverImage;

        return $this;
    }

    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    public function setAuthor(Author $author): static
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection<int, Category>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(Category $category): static
    {
        $this->categories->removeElement($category);

        return $this;
    }

    public function __toString(): string
    {
        return $this->title ?? '/';
    }
}
