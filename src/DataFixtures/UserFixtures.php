<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $manager->persist($this->createUser(
            'test1@test.com',
            'Test123#'
        ));

        $manager->persist($this->createUser(
            'test11@test.com',
            'Test123#'
        ));

        $admin = $this->createUser(
            'admin@test.com',
            'Test123#'
        );

        $manager->persist($admin->setRoles(
            array_merge(
                $admin->getRoles(),
                ['ROLE_ADMIN']
            )
        ));

        $manager->flush();
    }

    private function createUser(string $username, string $password): User
    {
        $user = new User();
        $user->setEmail($username);
        $user->setLanguage('en');
        $user->setPassword(
            $this->userPasswordHasher->hashPassword(
                $user,
                $password
            )
        );

        return $user;
    }
}
