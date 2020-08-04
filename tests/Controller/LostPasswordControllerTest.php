<?php

use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class LostPasswordControllerTest extends WebTestCase
{
    use FixturesTrait;

    public function testResetPasswordPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/reset-password');
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorNotExists('.alert.alert-danger');
    }

    public function testCheckMailPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/reset-password/check-email');
        $this->assertResponseRedirects('/reset-password');
    }

    public function testResetPasswordMailNotExist(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/reset-password');
        $form = $crawler->selectButton('Send password reset email')->form([
            'reset_password_request_form[email]' => 'test@blabla.com',
        ]);
        $client->submit($form);
        $this->assertResponseRedirects('/reset-password/check-email');
    }

    public function testResetPasswordMailExist(): void
    {
        $client = static::createClient();
        $client->enableProfiler();

        $users = $this->loadFixtureFiles([__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'UsersFixtures.yaml']);
        $crawler = $client->request('GET', '/reset-password');
        $form = $crawler->selectButton('Send password reset email')->form([
            'reset_password_request_form[email]' => $users['user_admin']->getEmail(),
        ]);
        $client->submit($form);
        $this->assertResponseRedirects('/reset-password/check-email');
        $crawler = $client->followRedirect();
        /*
                $mailCollector = $client->getProfile()->getCollector('swiftmailer');
                $this->assertSame(1, $mailCollector->getMessageCount(), 'Mail not send on lost password request!');
                /** @var \Swift_Message $message *
                $message = $mailCollector->getMessages()[0];
                $crawlerHTML = new Crawler($message->getBody());
                $url = $crawlerHTML->filter('a')->extract(['href']);
                $client->request('GET', $url[0]);
                $crawler = $client->followRedirect();
                $form = $crawler->selectButton('Reset password')->form([
                    'change_password_form[plainPassword][first]' => '123456789',
                    'change_password_form[plainPassword][second]' => '123456789',
                ]);
                $client->submit($form);
                $client->followRedirect();

                $kernel = $client->getKernel();
                $resetPasswordHelper = $kernel->getContainer()->get('symfonycasts.reset_password.helper');
                $resetToken = $resetPasswordHelper->generateResetToken($users['user_admin']);
                $data = [];
                $data['email'] = $users['user_admin']->getEmail();
                $data['password'] = '123456789';
                $data['_token'] = $resetToken;

                $crawler = $client->request('POST', '/login', $data);
                $crawler = $client->followRedirect();
                $this->assertResponseRedirects('/admin');
        */
    }
}
