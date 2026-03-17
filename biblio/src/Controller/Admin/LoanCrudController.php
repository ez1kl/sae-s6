<?php

namespace App\Controller\Admin;

use App\Entity\Loan;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;

class LoanCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Loan::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Emprunt')
            ->setEntityLabelInPlural('Emprunts')
            ->setPageTitle('index', 'Liste des emprunts')
            ->setPageTitle('new', 'Nouvel emprunt')
            ->setPageTitle('edit', 'Modifier l\'emprunt');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('book')->setLabel('Livre'),
            AssociationField::new('member')->setLabel('Membre'),
            DateTimeField::new('loanDate')->setLabel('Date de prêt')
                ->setFormTypeOption('data', new \DateTime()),
            DateTimeField::new('dueDate')->setLabel('Date de retour prévue')
                ->hideOnForm(),
            DateTimeField::new('returnDate')->setLabel('Date de retour effectif'),
        ];
    }
}
