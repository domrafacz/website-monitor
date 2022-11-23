<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('test1@test.com');
        $user->setLanguage('en');
        $user->setPassword(
            $this->userPasswordHasher->hashPassword(
                $user,
                'Test123#'
            )
        );
        $manager->persist($user);

        $user2 = new User();
        $user2->setEmail('test11@test.com');
        $user2->setLanguage('en');
        $user2->setPassword(
            $this->userPasswordHasher->hashPassword(
                $user2,
                'Test123#'
            )
        );
        $manager->persist($user2);

        $manager->flush();
    }
}
