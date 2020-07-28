<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use App\Tests\NeedLogin;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class RegistrationControllerTest extends WebTestCase
{
    use NeedLogin;
    use FixturesTrait;

    public function testRegisterPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorNotExists('.alert.alert-danger');
    }

    public function testRegistrationMailAlreadyExist(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');
        $form = $crawler->selectButton('Register')->form([
            'registration_form[firstname]' => 'firstname',
            'registration_form[lastname]' => 'lastname',
            'registration_form[email]' => 'superadmin@csf.com',
            'registration_form[plainPassword]' => '1234567',
            'registration_form[agreeTerms]' => 1,
        ]);
        $client->submit($form);
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testRegistrationPasswordTooShort(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');
        $form = $crawler->selectButton('Register')->form([
            'registration_form[firstname]' => 'firstname',
            'registration_form[lastname]' => 'lastname',
            'registration_form[email]' => 'superadmin@csf.com',
            'registration_form[plainPassword]' => '123',
            'registration_form[agreeTerms]' => 1,
        ]);
        $client->submit($form);
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testRegistrationSuccess(): void
    {
        $client = static::createClient();
        $client->enableProfiler();
        $crawler = $client->request('GET', '/register');
        $form = $crawler->selectButton('Register')->form([
            'registration_form[firstname]' => 'firstname',
            'registration_form[lastname]' => 'lastname',
            'registration_form[email]' => 'test@test.com',
            'registration_form[plainPassword]' => '12345654',
            'registration_form[agreeTerms]' => 1,
        ]);
        $client->submit($form);
        $this->assertResponseRedirects('/login');

        $mailCollector = $client->getProfile()->getCollector('swiftmailer');
        $this->assertSame(1, $mailCollector->getMessageCount(), 'Mail not send on user registration!');

        $user = $this->getContainer()->get(UserRepository::class)->findOneBy(['email' => 'test@test.com']);
        $this->login($client, $user);

        /** @var \Swift_Message $message */
        $message = $mailCollector->getMessages()[0];
        $crawlerHTML = new Crawler($message->getBody());
        $url = $crawlerHTML->filter('a')->extract(['href']);
        $client->request('GET', $url[0]);
        $client->followRedirect();

        $this->assertSelectorExists('.alert.alert-success');

        $user = $this->getContainer()->get(UserRepository::class)->findOneBy(['email' => 'test@test.com']);
        $this->assertTrue($user->isVerified());
    }
}
