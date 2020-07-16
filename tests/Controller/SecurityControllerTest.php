<?php
namespace App\Tests\Controller;

use App\Tests\NeedLogin;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    use NeedLogin;
    use FixturesTrait;

    public function testLoginPage()
    {
        $client = static::createClient();
        $client->request('GET', '/login');
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorNotExists('.alert.alert-danger');
    }

    public function testLoginBadCredentials()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Log in')->form([
            'email' => 'test@test.com',
            'password' => '12345',
        ]);
        $client->submit($form);
        $this->assertResponseRedirects('/login');
        $client->followRedirect();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testLoginSuccessFull()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/login');
        $form = $crawler->selectButton('Log in')->form([
            'email' => 'superadmin@csf.com',
            'password' => 'superadmin',
        ]);
        $client->submit($form);
        $this->assertResponseRedirects('/admin');
    }

    public function testProfilePageLoggedUser()
    {
        $client = static::createClient();
        $users = $this->loadFixtureFiles([__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'UsersFixtures.yaml']);
        $this->login($client, $users['user_superadmin']);
        $client->request('GET', '/profile');
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorNotExists('.alert.alert-danger');
    }

    public function testProfilePageNoLoggedUser()
    {
        $client = static::createClient();
        $client->request('GET', '/profile');
        $this->assertResponseRedirects('/login');
    }

    public function testProfileUpdateMailAlreadyExist()
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