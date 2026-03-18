<?php

namespace App\Controller\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
class UserCrudController extends AbstractCrudController
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('email'),
            TextField::new('plainPassword', 'Mot de passe')
                ->onlyWhenCreating()
                ->setFormType(RepeatedType::class)
                ->setFormTypeOption('type', PasswordType::class)
                ->setFormTypeOption('required', true)
                ->setFormTypeOption('invalid_message', 'Les mots de passe doivent correspondre.')
                ->setFormTypeOption('first_options', [
                    'label' => 'Mot de passe',
                    'attr' => ['autocomplete' => 'new-password'],
                ])
                ->setFormTypeOption('second_options', [
                    'label' => 'Confirmer le mot de passe',
                    'attr' => ['autocomplete' => 'new-password'],
                ]),
            TextField::new('plainPassword', 'Mot de passe')
                ->onlyWhenUpdating()
                ->setFormType(RepeatedType::class)
                ->setFormTypeOption('type', PasswordType::class)
                ->setFormTypeOption('required', false)
                ->setFormTypeOption('invalid_message', 'Les mots de passe doivent correspondre.')
                ->setFormTypeOption('first_options', [
                    'label' => 'Nouveau mot de passe (laisser vide pour conserver)',
                    'attr' => ['autocomplete' => 'new-password'],
                ])
                ->setFormTypeOption('second_options', [
                    'label' => 'Confirmer le nouveau mot de passe',
                    'attr' => ['autocomplete' => 'new-password'],
                ]),
            ChoiceField::new('roles')
                ->setChoices([
                    'Membre' => 'ROLE_MEMBRE',
                    'Bibliothécaire' => 'ROLE_BIBLIOTHECAIRE',
                    'Responsable' => 'ROLE_RESPONSABLE',
                ])
                ->allowMultipleChoices()
                ->renderExpanded(),
            AssociationField::new('member')->setLabel('Profil membre')->hideOnForm(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User) {
            $plainPassword = $entityInstance->getPlainPassword();
            if (is_string($plainPassword) && trim($plainPassword) !== '') {
                $entityInstance->setPassword(
                    $this->passwordHasher->hashPassword($entityInstance, trim($plainPassword))
                );
            }
            $entityInstance->setPlainPassword(null);
        }
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User) {
            $plainPassword = $entityInstance->getPlainPassword();
            if (is_string($plainPassword) && trim($plainPassword) !== '') {
                $entityInstance->setPassword(
                    $this->passwordHasher->hashPassword($entityInstance, trim($plainPassword))
                );
            }
            $entityInstance->setPlainPassword(null);
        }
        parent::updateEntity($entityManager, $entityInstance);
    }
}