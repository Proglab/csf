<?php
/**
 * Created by PhpStorm.
 * User: fafag
 * Date: 15-07-20
 * Time: 11:52.
 */

namespace App\Tests\Repository;

use App\DataFixtures\UsersFixtures;
use App\Entity\User;
use App\Repository\UserRepository;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    use FixturesTrait;

    public function testCount()
    {
        self::bootKernel();
        $this->loadFixtures([UsersFixtures::class]);
        $users = self::$container->get(UserRepository::class)->count([]);
        $this->assertEquals(10, $users);
    }

    public function testUpgradePassword()
    {
        self::bootKernel();
        $this->loadFixtures([UsersFixtures::class]);
        /** @var UserRepository $repository */
        $repository = self::$container->get(UserRepository::class);
        /** @var User $user */
        $user = $repository->findOneBy(['email' => 'superadmin@csf.com']);
        $password = $user->getPassword();
        $newPassword = self::$container->get('security.password_encoder')->encodePassword(
            $user,
            '1234567'
        );
        $repository->upgradePassword($user, $newPassword);
        $this->assertNotEquals($password, $user->getPassword());
    }
}
