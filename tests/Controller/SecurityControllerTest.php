<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Tests\NeedLogin;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    use NeedLogin;
    use FixturesTrait;

    public function testLoginPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorNotExists('.alert.alert-danger');
    }

    public function testLoginBadCredentials(): void
    {
        $client = static::createClient();
        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('authenticate');
        $data = [];
        $data['email'] = 'test@test.com';
        $data['password'] = '12345678';
        $data['_csrf_token'] = $csrfToken;

        $crawler = $client->request('POST', '/login', $data);
        $client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testLoginSuccessFull(): void
    {
        $client = static::createClient();
        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->getToken('authenticate');
        /** @var User $user * */
        $user = $this->getContainer()->get(UserRepository::class)->findOneBy(['email' => 'superadmin@csf.com']);
        $data['email'] = $user->getEmail();
        $data['password'] = 'superadmin';
        $data['_csrf_token'] = $csrfToken;
        $crawler = $client->request('POST', '/login', $data);
        $this->assertResponseRedirects('/admin');
        $this->assertInstanceOf(\DateTime::class, $user->getLastConnection());
    }

    public function testProfilePageLoggedUser(): void
    {
        $client = static::createClient();
        $users = $this->loadFixtureFiles([__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'UsersFixtures.yaml']);
        $this->login($client, $users['user_superadmin']);
        $client->request('GET', '/profile');
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorNotExists('.alert.alert-danger');
    }

    public function testProfilePageNoLoggedUser(): void
    {
        $client = static::createClient();
        $client->request('GET', '/profile');
        $this->assertResponseRedirects('/login');
    }

    public function testProfileUpdateMailAlreadyExist(): void
    {
        $client = static::createClient();
        $users = $this->loadFixtureFiles([__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'UsersFixtures.yaml']);

        $this->login($client, $users['user_superadmin']);
        $crawler = $client->request('GET', '/profile');
        $form = $crawler->selectButton('Update')->form([
            'profile_form[email]' => 'admin@csf.com',
        ]);
        $client->submit($form);
        $this->assertSelectorExists('.alert.alert-danger');
    }
}
