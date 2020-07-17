<?php

namespace App\Tests\Entity;

use App\Entity\User;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class UserEntityTest extends KernelTestCase
{
    use FixturesTrait;

    public function getEntity(): User
    {
        $user = new User();
        $user->setFirstname('Fabrice');
        $user->setLastname('Gyre');
        $user->setIsVerified(1);
        $user->setPlainPassword('test123');
        $user->setPassword('312312546');
        $user->setEmail('fabrice@proglab.com');
        $user->setRoles(['ROLE_USER']);

        return $user;
    }

    public function getErrors(ConstraintViolationList $errors): string
    {
        $messages = [];
        /** @var ConstraintViolation $error */
        foreach ($errors as $error) {
            $messages[] = $error->getPropertyPath().' => '.$error->getMessage();
        }

        return implode("\n", $messages);
    }

    public function testValidEntity(): void
    {
        $user = $this->getEntity();
        self::bootKernel();
        $errors = self::$container->get('validator')->validate($user);
        $this->assertCount(0, $errors, $this->getErrors($errors));
    }

    public function testInvalidEntity(): void
    {
        $user = $this->getEntity();
        $user->setEmail('testdemail');
        self::bootKernel();
        $errors = self::$container->get('validator')->validate($user);
        $this->assertCount(1, $errors, $this->getErrors($errors));
    }

    public function testDuplicateMail(): void
    {
        self::bootKernel();
        $users = $this->loadFixtureFiles([__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'Admin'.DIRECTORY_SEPARATOR.'UsersFixtures.yaml']);
        $user = $this->getEntity();
        $user->setEmail('superadmin@csf.com');
        $errors = self::$container->get('validator')->validate($user);
        $this->assertCount(1, $errors, $this->getErrors($errors));
    }
}
