<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthControllerTest extends WebTestCase
{
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

}