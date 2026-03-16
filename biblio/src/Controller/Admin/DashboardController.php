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
        yield MenuItem::section('Guichet Rapide', 'fas fa-desktop');
        yield MenuItem::linkToRoute('Espace Bibliothécaire', 'fas fa-id-card', 'admin_librarian_dashboard');
        
        yield MenuItem::section('Gestion Catalogue', 'fas fa-layer-group');
        yield MenuItem::linkToRoute('Auteurs', 'fas fa-user-edit', 'admin_author_index');
        yield MenuItem::linkToRoute('Livres', 'fas fa-book', 'admin_book_index');
        yield MenuItem::linkToRoute('Catégories', 'fas fa-tags', 'admin_category_index');

        yield MenuItem::section('Membres & Prêts', 'fas fa-users');
        yield MenuItem::linkToRoute('Membres', 'fas fa-address-book', 'admin_member_index');
        yield MenuItem::linkToRoute('Emprunts', 'fas fa-hand-holding', 'admin_loan_index');
        yield MenuItem::linkToRoute('Réservations', 'fas fa-bookmark', 'admin_reservation_index');
        
        yield MenuItem::section('Utilisateurs Authentification', 'fas fa-shield-alt');
        yield MenuItem::linkToRoute('Profils Système', 'fas fa-user-shield', 'admin_user_index');
    }
}
