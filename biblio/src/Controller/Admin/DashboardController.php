<?php

namespace App\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Category;
use App\Entity\Loan;
use App\Entity\Member;
use App\Entity\Reservation;
use App\Entity\User;


#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(private EntityManagerInterface $em) {}

    public function index(): Response
    {
        $nbAuthors = $this->em->getRepository(Author::class)->count([]);
        $nbBooks = $this->em->getRepository(Book::class)->count([]);
        $nbMembers = $this->em->getRepository(Member::class)->count([]);
        $nbLoans = $this->em->getRepository(Loan::class)->count([]);
        $nbReservations = $this->em->getRepository(Reservation::class)->count([]);
        $nbCategories = $this->em->getRepository(Category::class)->count([]);

        return $this->render('Admin/dashboard.html.twig', [
            'nbAuthors' => $nbAuthors,
            'nbBooks' => $nbBooks,
            'nbMembers' => $nbMembers,
            'nbLoans' => $nbLoans,
            'nbReservations' => $nbReservations,
            'nbCategories' => $nbCategories,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Bibliothèque - Administration');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToRoute('Auteurs', 'fas fa-tags', 'admin_author_index');
        yield MenuItem::linkToRoute('Livres', 'fas fa-newspaper', 'admin_book_index');
        yield MenuItem::linkToRoute('Membres', 'fas fa-users', 'admin_member_index');
        yield MenuItem::linkToRoute('Emprunts', 'fas fa-users', 'admin_loan_index');
        yield MenuItem::linkToRoute('Réservations', 'fas fa-users', 'admin_reservation_index');
        yield MenuItem::linkToRoute('Catégories', 'fas fa-tags', 'admin_category_index');
    }
}
