<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class UserCrudController extends AbstractCrudController
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->update(Crud::PAGE_INDEX, Action::EDIT , function (Action $action) {
                return $action->setIcon('fa fa-edit')->setLabel(false);
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE , function (Action $action) {
                return $action->setIcon('fa fa-trash')->setLabel(false);
            })
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('firstname')->hideOnIndex(),
            TextField::new('lastname')->hideOnIndex(),
            TextField::new('fullName')->hideOnForm(),
            EmailField::new('email'),
            TextField::new('plainPassword', 'Password')->hideOnIndex(),
            BooleanField::new('isVerified'),
        ];
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param User $entityInstance
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if(!empty($entityInstance->getPlainPassword())) {
            $entityInstance->setPassword(
                $this->passwordEncoder->encodePassword(
                    $entityInstance,
                    $entityInstance->getPlainPassword()
                )
            );
        }
        parent::persistEntity($entityManager, $entityInstance);
    }

}
