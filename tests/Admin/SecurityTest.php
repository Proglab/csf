<?php

namespace App\Tests\Admin;

use App\Tests\NeedLogin;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityTest extends WebTestCase
{
    use FixturesTrait;
    use NeedLogin;

    public function testAdminDashboardPageAccessToAnonymousUser()
    {
        $client = static::createClient();
        $client->request('GET', '/admin');
        $this->assertResponseRedirects('/login');
    }

    public function testAdminDashboardPageAccessToAuthenticatedAdmin()
    {
        $client = static::createClient();
        $users = $this->loadFixtureFiles([__DIR__.DIRECTORY_SEPARATOR.'UsersFixtures.yaml']);
        $this->login($client, $users['user_admin']);

        $client->request('GET', '/admin');
        $this->assertResponseStatusCodeSame(200);
    }

    public function testAdminDashboardPageAccessToAuthenticatedSuperadmin()
    {
        $client = static::createClient();
        $users = $this->loadFixtureFiles([__DIR__.DIRECTORY_SEPARATOR.'UsersFixtures.yaml']);
        $this->login($client, $users['user_superadmin']);

        $client->request('GET', '/admin');
        $this->assertResponseStatusCodeSame(200);
    }

    public function testAdminDashboardPageAccessToAuthenticatedUser()
    {
        $client = static::createClient();
        $users = $this->loadFixtureFiles([__DIR__.DIRECTORY_SEPARATOR.'UsersFixtures.yaml']);
        $this->login($client, $users['user_user']);

        $client->request('GET', '/');
        $this->assertResponseStatusCodeSame(200);
    }
}
