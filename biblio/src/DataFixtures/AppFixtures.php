<?php

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Category;
use App\Entity\Loan;
use App\Entity\Member;
use App\Entity\Reservation;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // --- 5 Auteurs ---
        $authors = [];
        $authorData = [
            ['Hugo', 'Victor', '1802-02-26', '1885-05-22', 'Française'],
            ['Camus', 'Albert', '1913-11-07', '1960-01-04', 'Française'],
            ['Austen', 'Jane', '1775-12-16', '1817-07-18', 'Britannique'],
            ['García Márquez', 'Gabriel', '1927-03-06', '2014-04-17', 'Colombienne'],
            ['Murakami', 'Haruki', '1949-01-12', null, 'Japonaise'],
        ];

        foreach ($authorData as [$lastName, $firstName, $birth, $death, $nationality]) {
            $author = new Author();
            $author->setLastName($lastName);
            $author->setFirstName($firstName);
            $author->setBirthDate(new \DateTime($birth));
            $author->setDeathDate($death ? new \DateTime($death) : null);
            $author->setNationality($nationality);
            $manager->persist($author);
            $authors[] = $author;
        }

        // --- 5 Catégories ---
        $categories = [];
        $categoryData = [
            ['Roman', 'Œuvres de fiction narrative en prose.'],
            ['Science-fiction', 'Récits basés sur des avancées scientifiques ou technologiques imaginaires.'],
            ['Philosophie', 'Ouvrages traitant de questions fondamentales sur l\'existence et la connaissance.'],
            ['Poésie', 'Œuvres littéraires en vers ou en prose poétique.'],
            ['Littérature étrangère', 'Traductions d\'œuvres majeures de la littérature mondiale.'],
        ];

        foreach ($categoryData as [$name, $description]) {
            $category = new Category();
            $category->setName($name);
            $category->setDescription($description);
            $manager->persist($category);
            $categories[] = $category;
        }

        // --- 20 Livres ---
        $books = [];
        $bookData = [
            ['Les Misérables', 1862, 'fr', 0, [0]],
            ['Notre-Dame de Paris', 1831, 'fr', 0, [0]],
            ['Les Contemplations', 1856, 'fr', 0, [3]],
            ['L\'Étranger', 1942, 'fr', 1, [0, 2]],
            ['La Peste', 1947, 'fr', 1, [0]],
            ['Le Mythe de Sisyphe', 1942, 'fr', 1, [2]],
            ['Orgueil et Préjugés', 1813, 'en', 2, [0, 4]],
            ['Raison et Sentiments', 1811, 'en', 2, [0, 4]],
            ['Emma', 1815, 'en', 2, [0, 4]],
            ['Cent ans de solitude', 1967, 'es', 3, [0, 4]],
            ['L\'Amour aux temps du choléra', 1985, 'es', 3, [0, 4]],
            ['Chronique d\'une mort annoncée', 1981, 'es', 3, [0, 4]],
            ['Kafka sur le rivage', 2002, 'ja', 4, [0, 1, 4]],
            ['1Q84', 2009, 'ja', 4, [0, 1, 4]],
            ['La Ballade de l\'impossible', 1987, 'ja', 4, [0, 4]],
            ['Les Travailleurs de la mer', 1866, 'fr', 0, [0]],
            ['La Chute', 1956, 'fr', 1, [0, 2]],
            ['Mansfield Park', 1814, 'en', 2, [0, 4]],
            ['Des feuilles mortes', 1955, 'es', 3, [0, 4]],
            ['Après le tremblement de terre', 2000, 'ja', 4, [1, 4]],
        ];

        foreach ($bookData as [$title, $year, $lang, $authorIdx, $catIdxs]) {
            $book = new Book();
            $book->setTitle($title);
            $book->setReleaseYear($year);
            $book->setLanguage($lang);
            $book->setAuthor($authors[$authorIdx]);
            foreach ($catIdxs as $ci) {
                $book->addCategory($categories[$ci]);
            }
            $manager->persist($book);
            $books[] = $book;
        }

        // --- 10 Adhérents (+ Users) ---
        $members = [];
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setEmail($faker->unique()->safeEmail());
            $user->setRoles(['ROLE_MEMBRE']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'user'));
            $manager->persist($user);

            $member = new Member();
            $member->setUser($user);
            $member->setLastName($faker->lastName());
            $member->setFirstName($faker->firstName());
            $member->setBirthDate($faker->dateTimeBetween('-60 years', '-18 years'));
            $member->setMembershipDate($faker->dateTimeBetween('-3 years', 'now'));
            $member->setPhoneNumber($faker->phoneNumber());
            $member->setAddress($faker->address());
            // Suspendre 2 adhérents pour tester
            if ($i >= 8) {
                $member->setSuspended(true);
            }
            $manager->persist($member);
            $members[] = $member;
        }

        // Administrateur (ROLE_RESPONSABLE)
        $adminUser = new User();
        $adminUser->setEmail('admin@admin.fr');
        $adminUser->setRoles(['ROLE_RESPONSABLE']);
        $adminUser->setPassword($this->passwordHasher->hashPassword($adminUser, 'admin'));
        $manager->persist($adminUser);

        // Bibliothécaire (ROLE_BIBLIOTHECAIRE)
        $libUser = new User();
        $libUser->setEmail('biblio@biblio.fr');
        $libUser->setRoles(['ROLE_BIBLIOTHECAIRE']);
        $libUser->setPassword($this->passwordHasher->hashPassword($libUser, 'biblio'));
        $manager->persist($libUser);

        // --- 15 Emprunts variés ---
        // Mix: emprunts rendus, en cours, en retard
        $usedBooksForActiveLoans = [];
        for ($i = 0; $i < 15; $i++) {
            $loan = new Loan();
            $member = $faker->randomElement($members);

            // Pick a book, allowing re-borrowing for returned loans
            $book = $books[$i % count($books)];

            $loan->setMember($member);
            $loan->setBook($book);

            if ($i < 7) {
                // Emprunts rendus (passés)
                $loanDate = $faker->dateTimeBetween('-6 months', '-2 months');
                $dueDate = (clone $loanDate)->modify('+14 days');
                $returnDate = (clone $loanDate)->modify('+' . $faker->numberBetween(5, 20) . ' days');
                $loan->setLoanDate($loanDate);
                $loan->setDueDate($dueDate);
                $loan->setReturnDate($returnDate);
            } elseif ($i < 12) {
                // Emprunts en cours (non rendus, pas encore en retard)
                $loanDate = $faker->dateTimeBetween('-10 days', '-2 days');
                $dueDate = (clone $loanDate)->modify('+14 days');
                $loan->setLoanDate($loanDate);
                $loan->setDueDate($dueDate);
                // returnDate reste null
            } else {
                // Emprunts en retard (non rendus, date de retour dépassée)
                $loanDate = $faker->dateTimeBetween('-2 months', '-1 month');
                $dueDate = (clone $loanDate)->modify('+14 days');
                $loan->setLoanDate($loanDate);
                $loan->setDueDate($dueDate);
                // returnDate reste null
            }

            $manager->persist($loan);
        }

        // --- 3 Réservations ---
        // Utiliser des livres qui ne sont pas actuellement empruntés (index 15, 16, 17)
        $reservationBooks = [$books[15], $books[16], $books[17]];
        for ($i = 0; $i < 3; $i++) {
            $reservation = new Reservation();
            $reservation->setBook($reservationBooks[$i]);
            $reservation->setMember($members[$i]);
            // createdAt est défini automatiquement dans le constructeur
            $manager->persist($reservation);
        }

        $manager->flush();
    }
}
