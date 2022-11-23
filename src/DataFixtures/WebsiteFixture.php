<?php
declare(strict_types=1);

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
        $website->setTimeout(10);
        $website->setFrequency(1);
        $website->setEnabled(true);
        $website->setOwner($user);

        $manager->persist($website);

        $website2 = new Website();
        $website2->setUrl('https://nonexistent.nonexistent');
        $website2->setRequestMethod('GET');
        $website2->setMaxRedirects(20);
        $website2->setTimeout(2);
        $website2->setFrequency(1);
        $website2->setEnabled(true);
        $website2->setOwner($user);

        $manager->persist($website2);

        $website3 = new Website();
        $website3->setUrl('https://www.koreatimes.co.kr/');
        $website3->setRequestMethod('GET');
        $website3->setMaxRedirects(20);
        $website3->setTimeout(0);
        $website3->setFrequency(1);
        $website3->setEnabled(true);
        $website3->setOwner($user);

        $manager->persist($website3);

        $user2 = $this->userRepository->findOneByUsername('test11@test.com');

        if (!$user2) {
            throw new UserNotFoundException('test11@test.com');
        }

        $website4 = new Website();
        $website4->setUrl('https://google.com');
        $website4->setRequestMethod('GET');
        $website4->setMaxRedirects(20);
        $website4->setTimeout(0);
        $website4->setFrequency(1);
        $website4->setEnabled(true);
        $website4->setOwner($user2);

        $manager->persist($website4);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixture::class,
        ];
    }
}