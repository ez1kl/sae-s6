<?php

namespace App\Controller\Admin;

use App\Entity\Member;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class MemberCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Member::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Membre')
            ->setEntityLabelInPlural('Membres')
            ->setPageTitle('index', 'Liste des membres')
            ->setPageTitle('new', 'Nouveau membre')
            ->setPageTitle('edit', 'Modifier le membre');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            DateField::new('membershipDate')->setLabel('Date d\'adhésion')
                ->setFormTypeOption('data', new \DateTime()),
            AssociationField::new('user')->setLabel('Utilisateur')->autocomplete(),
            TextField::new('lastName')->setLabel('Nom'),
            TextField::new('firstName')->setLabel('Prénom'),
            DateField::new('birthDate')->setLabel('Date de naissance'),
            TextField::new('phoneNumber')->setLabel('Téléphone'),
            TextField::new('address')->setLabel('Adresse'),
            BooleanField::new('suspended')->setLabel('Suspendu'),
        ];
    }
}
