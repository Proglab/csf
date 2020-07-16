<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    public function testRegisterPage()
    {
        $client = static::createClient();
        $client->request('GET', '/register');
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorNotExists('.alert.alert-danger');
    }

    public function testRegistrationMailAlreadyExist()
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

    public function testRegistrationPasswordTooShort()
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

    public function testRegistrationSuccess()
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

        $mailCollector = $client->getProfile()->getCollector('swiftmailer');
        $this->assertSame(1, $mailCollector->getMessageCount());
        $this->assertResponseRedirects('/admin');
    }
}
