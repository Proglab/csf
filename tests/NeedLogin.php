<?php

namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

trait NeedLogin
{
    public function login(KernelBrowser $client, User $user, string $firewall = 'main')
    {
        $session = $client->getContainer()->get('session');
        $token = new UsernamePasswordToken($user, null, $firewall, $user->getRoles());
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        $ccokie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($ccokie);
    }
}
