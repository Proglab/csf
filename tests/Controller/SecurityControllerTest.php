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

    /**
    public function testLoginBadCredentials(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        $client->submitForm('Log in', [
            'email' => 'test@test.com',
            'password' => '12345',
        ]);
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testLoginSuccessFull(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        /** @var User $user *'/
        $user = $this->getContainer()->get(UserRepository::class)->findOneBy(['email' => 'superadmin@csf.com']);
        $form = $crawler->selectButton('Log in')->form([
            'email' => $user->getEmail(),
            'password' => 'superadmin',
        ]);
        $client->submit($form);
        $this->assertResponseRedirects('/admin');
        $this->assertInstanceOf(\DateTime::class, $user->getLastConnection());
    }
     */
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
