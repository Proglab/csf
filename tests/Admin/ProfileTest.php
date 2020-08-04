<?php

namespace App\Tests\Admin;

use App\Controller\Admin\ProfileCrudController;
use App\Entity\User;
use App\Tests\NeedLogin;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProfileTest extends WebTestCase
{
    use FixturesTrait;
    use NeedLogin;

    public function testAdminProfile()
    {
        $client = static::createClient();

        /**
         * @var array<string, User> $users
         */
        $users = $this->loadFixtureFiles([__DIR__.DIRECTORY_SEPARATOR.'UsersFixtures.yaml']);
        $this->login($client, $users['user_admin']);

        $router = $this->getContainer()->get('EasyCorp\Bundle\EasyAdminBundle\Router\CrudUrlGenerator');
        $url = $router->build()->setController(ProfileCrudController::class)->setAction(Action::EDIT)->set('entityId', $users['user_admin']->getId());

        $crawler = $client->request('GET', $url);
        $this->assertResponseStatusCodeSame(200);

        $form = $crawler->filter('button.action-save')->form([
            'User[firstname]' => $users['user_superadmin']->getFirstname(),
            'User[lastname]' => $users['user_superadmin']->getLastname(),
            'User[email]' => $users['user_superadmin']->getEmail(),
            'User[plainPassword]' => '123456789',
        ]);
        $client->submit($form);

        $this->assertSelectorExists('.form-error-message');

        $form = $crawler->filter('button.action-save')->form([
            'User[firstname]' => $users['user_admin']->getFirstname(),
            'User[lastname]' => $users['user_admin']->getLastname(),
            'User[email]' => $users['user_admin']->getEmail(),
            'User[plainPassword]' => '123456789',
        ]);
        $client->submit($form);
        $this->assertResponseRedirects('/login');
        $client->followRedirect();
        $client->request('GET', '/logout');
        $client->followRedirect();

        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('authenticate');
        $data['email'] = $users['user_admin']->getEmail();
        $data['password'] = 'admin';
        $data['_csrf_token'] = $csrfToken;
        $client->request('POST', '/login', $data);
        $this->assertResponseRedirects('/login');
        $client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
        /*
                $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('authenticate');
                $data['email'] = $users['user_admin']->getEmail();
                $data['password'] = '123456789';
                $data['_csrf_token'] = $csrfToken;
                $client->request('POST', '/login', $data);
                $this->assertResponseRedirects('/admin');
        */
    }

    public function testAdminProfileAccessDenied()
    {
        $client = static::createClient();
        $users = $this->loadFixtureFiles([__DIR__.DIRECTORY_SEPARATOR.'UsersFixtures.yaml']);
        $this->login($client, $users['user_admin']);

        $router = $this->getContainer()->get('EasyCorp\Bundle\EasyAdminBundle\Router\CrudUrlGenerator');
        $url = $router->build()->setController(ProfileCrudController::class)->setAction(Action::EDIT)->set('entityId', $users['user_superadmin']->getId());

        $crawler = $client->request('GET', $url);
        $this->assertResponseStatusCodeSame(403);
    }
}
