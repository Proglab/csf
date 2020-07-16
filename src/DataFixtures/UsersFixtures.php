<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Nelmio\Alice\Loader\NativeLoader;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UsersFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $userPasswordEncoder;

    public function __construct(UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    public function load(ObjectManager $manager): void
    {
        $loader = new NativeLoader();
        $objectSet = $loader->loadFile(__DIR__.'/UsersFixtures.yaml');
        foreach ($objectSet->getObjects() as $user) {
            $user->setPassword($this->userPasswordEncoder->encodePassword(
                $user,
                $user->getPlainPassword()
            ));

            $manager->persist($user);
        }
        $manager->flush();
    }
}
