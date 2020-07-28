<?php

namespace App\Tests\Admin;

use App\Tests\NeedLogin;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminCrudControllerTest extends WebTestCase
{
    use FixturesTrait;
    use NeedLogin;
    protected $url_crawled = [''];
    protected $url_banned = ['https://symfony.com', 'https://trello.com', 'https://github.com', '#', 'mailto', '/?', '/logout'];

    public function testAdminPage()
    {
        $client = static::createClient();
        $client->request('GET', '/admin');
        $this->assertResponseRedirects('/login');
    }

    public function testAdminPageAccessToAuthenticatedUser()
    {
        $client = static::createClient();
        $users = $this->loadFixtureFiles([__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'UsersFixtures.yaml']);
        $this->login($client, $users['user_admin']);

        $client->request('GET', '/admin');
        $this->assertResponseStatusCodeSame(200);
    }

    public function testCrawlAdmin()
    {
        $client = static::createClient();
        $users = $this->loadFixtureFiles([__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'UsersFixtures.yaml']);
        $this->login($client, $users['user_admin']);

        $this->crawl('/admin', $client);
    }

    private function crawl($url, $client)
    {
        foreach ($this->url_banned as $url_banned) {
            if (0 === strpos($url, $url_banned)) {
                return;
            }
        }

        if (in_array($url, $this->url_crawled)) {
            return;
        }

        if (false !== strpos($url, 'crudAction=delete')) {
            return;
        } else {
            $this->url_crawled[] = $url;
            $client->request('GET', $url);
            $this->assertResponseStatusCodeSame(200, $url);
        }
        $crawler = $client->getCrawler();
        $urls = $crawler->filter('a');
        foreach ($urls as $link) {
            if (!in_array($link->getAttribute('href'), $this->url_crawled)) {
                $this->crawl($link->getAttribute('href'), $client);
            }
        }
    }
}
