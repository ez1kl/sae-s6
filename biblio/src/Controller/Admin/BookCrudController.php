<?php

namespace App\Controller\Admin;

use App\Entity\Book;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class BookCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Book::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Livre')
            ->setEntityLabelInPlural('Livres')
            ->setPageTitle('index', 'Liste des livres')
            ->setPageTitle('new', 'Nouveau livre')
            ->setPageTitle('edit', 'Modifier le livre');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('title')->setLabel('Titre'),
            IntegerField::new('releaseYear')->setLabel('Année de publication'),
            TextField::new('language')->setLabel('Langue'),
            AssociationField::new('author')->setLabel('Auteur'),
            AssociationField::new('categories')->setLabel('Catégories'),
            TextField::new('coverImage'),
        ];
    }
}
