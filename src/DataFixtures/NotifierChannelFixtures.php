<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\NotifierChannel;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class NotifierChannelFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $user = $this->userRepository->findOneByUsername('test1@test.com');

        if (!$user) {
            throw new UserNotFoundException('test1@test.com');
        }

        $notifierChannel = $this->createNotifierChannel(
            $user,
            0,
            'telegram',
            ['apiToken' => '123', 'chatId' => '456']
        );

        $manager->persist($notifierChannel);
        $manager->persist($user->addNotifierChannel($notifierChannel));

        $manager->flush();
    }

    private function createNotifierChannel(User $user, int $type, string $name, array $options = []): NotifierChannel
    {
        return new NotifierChannel(
            $user,
            $type,
            $name,
            $options,
        );
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}