<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
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
        foreach ($this->getDatas() as $data) {
            $user = new User();
            $user->setFirstname($data['firstname']);
            $user->setLastname($data['lastname']);
            $user->setEmail($data['email']);
            $user->setPassword($this->userPasswordEncoder->encodePassword(
                $user,
                $data['password']
            ));
            $user->setRoles($data['roles']);
            $user->setIsVerified($data['isVerified']);
            $manager->persist($user);
        }

        $manager->flush();
    }

    /**
     * @return array<array>
     */
    public function getDatas(): array
    {
        return [
            [
                'firstname' => 'Admin',
                'lastname' => 'istrator',
                'email' => 'info@proglab.com',
                'password' => 'admin',
                'roles' => ['ROLE_ADMIN'],
                'isVerified' => 1,
            ],
            [
                'firstname' => 'basic',
                'lastname' => 'user',
                'email' => 'manu@absolute-fx.com',
                'password' => 'user',
                'roles' => ['ROLE_USER'],
                'isVerified' => 1,
            ],
        ];
    }
}
