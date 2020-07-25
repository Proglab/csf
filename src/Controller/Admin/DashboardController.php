<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return parent::index();
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Csf');
    }

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::linktoDashboard('Dashboard', 'fa fa-home'),
            MenuItem::linktoRoute('Frontpage', 'fa fa-eye', 'homepage'),
            MenuItem::subMenu('Users', 'fa fa-users')->setSubItems([
                MenuItem::linkToCrud('User', 'fa fa-user', User::class)->setController(UserCrudController::class),
                MenuItem::linkToCrud('Admin', 'fa fa-user-shield', User::class)->setController(AdminCrudController::class)->setPermission('ROLE_ADMIN'),
                MenuItem::linkToCrud('Super Admin', 'fa fa-user-shield', User::class)->setController(SuperAdminCrudController::class)->setPermission('ROLE_SUPERADMIN'),
                MenuItem::linkToCrud('Not verified', 'fa fa-user-times', User::class)->setController(NotVerifiedCrudController::class)->setPermission('ROLE_ADMIN'),
            ]),
            MenuItem::linkToLogout('Logout', 'fa fa-fw fa-sign-out'),
        ];
    }

    /**
     * @param User $user
     */
    public function configureUserMenu(UserInterface $user): UserMenu
    {
        /** @var User $user */
        $user = $this->getUser();
        // Usually it's better to call the parent method because that gives you a
        // user menu with some menu items already created ("sign out", "exit impersonation", etc.)
        // if you prefer to create the user menu from scratch, use: return UserMenu::new()->...
        return parent::configureUserMenu($user)
            // use the given $user object to get the user name
            ->setName($user->getFullName())
            ->displayUserAvatar(false)
            // you can use any type of menu item, except submenus
            ->addMenuItems([
                MenuItem::linkToCrud('Profile', 'fa fa-user-circle', User::class)->setController(ProfileCrudController::class)->setAction('edit')->setEntityId($user->getId()),
            ]);
    }
}
