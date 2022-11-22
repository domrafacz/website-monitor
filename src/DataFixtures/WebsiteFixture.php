<?php

namespace App\DataFixtures;

use App\Entity\Website;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class WebsiteFixture extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $user = $this->userRepository->findOneByUsername('test1@test.com');

        if (!$user) {
            throw new UserNotFoundException('test1@test.com');
        }

        $website = new Website();
        $website->setUrl('https://google.com');
        $website->setRequestMethod('GET');
        $website->setMaxRedirects(0);
        $website->setTimeout(30);
        $website->setFrequency(1);
        $website->setEnabled(true);
        $website->setOwner($user);

        $manager->persist($website);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixture::class,
        ];
    }
}