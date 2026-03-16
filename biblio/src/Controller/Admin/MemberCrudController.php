<?php

namespace App\Controller\Admin;

use App\Entity\Member;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;

class MemberCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Member::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id') -> hideOnForm(),
            DateField::new('membershipDate') -> setFormTypeOption('data', new \DateTime()),
            AssociationField::new('user') -> autocomplete(),
            TextField::new('lastName'),
            TextField::new('firstName'),
            DateField::new('birthDate'),
            TextField::new('phoneNumber'),
            TextField::new('address'),
            BooleanField::new('suspended'),
        ];
    }
}
