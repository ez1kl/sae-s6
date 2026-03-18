<?php

namespace App\Controller\Admin;

use App\Entity\Author;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AuthorCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Author::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Auteur')
            ->setEntityLabelInPlural('Auteurs')
            ->setPageTitle('index', 'Liste des auteurs')
            ->setPageTitle('new', 'Nouvel auteur')
            ->setPageTitle('edit', 'Modifier l\'auteur');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('lastName')->setLabel('Nom'),
            TextField::new('firstName')->setLabel('Prénom'),
            TextField::new('nationality')->setLabel('Nationalité'),
            DateField::new('birthDate')->setLabel('Date de naissance'),
            DateField::new('deathDate')->setLabel('Date de décès'),
            TextField::new('photo')->setLabel('Photo'),
            TextareaField::new('description')->setLabel('Description'),
        ];
    }
}
