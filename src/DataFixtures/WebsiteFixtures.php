<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\DowntimeLog;
use App\Entity\ResponseLog;
use App\Entity\Website;
use App\Repository\UserRepository;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class WebsiteFixtures extends Fixture implements DependentFixtureInterface
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

        $time = new \DateTimeImmutable();
        //subtract 10 minutes
        // $time->sub(new \DateInterval("PT10M")

        $past = $time->modify('-50 day');

        $website = $this->createWebsite(
            $user,
            'https://google.com',
            'GET',
            302,
            0,
            10,
            1,
            true,
            $time->sub(new \DateInterval("PT10M"))
        );

        $manager->persist($website);


        $website2 = new Website();
        $website2->setUrl('https://nonexistent.nonexistent');
        $website2->setRequestMethod('GET');
        $website2->setMaxRedirects(20);
        $website2->setTimeout(2);
        $website2->setFrequency(1);
        $website2->setEnabled(true);
        $website2->setExpectedStatusCode(404);
        $website2->setOwner($user);
        $website2->setLastCheck($time->sub(new \DateInterval("PT10M")));

        $manager->persist($website2);

        $website3 = new Website();
        $website3->setUrl('https://www.koreatimes.co.kr/');
        $website3->setRequestMethod('GET');
        $website3->setMaxRedirects(20);
        $website3->setTimeout(1);
        $website3->setFrequency(1);
        $website3->setEnabled(true);
        $website3->setExpectedStatusCode(200);
        $website3->setOwner($user);
        $website3->setLastCheck($time->sub(new \DateInterval("PT10M")));

        $manager->persist($website3);

        $user2 = $this->userRepository->findOneByUsername('test11@test.com');

        if (!$user2) {
            throw new UserNotFoundException('test11@test.com');
        }

        $website4 = new Website();
        $website4->setUrl('https://bing.com');
        $website4->setRequestMethod('GET');
        $website4->setMaxRedirects(20);
        $website4->setTimeout(0);
        $website4->setFrequency(1);
        $website4->setEnabled(true);
        $website4->setExpectedStatusCode(200);
        $website4->setOwner($user2);
        $website4->setLastCheck($time->sub(new \DateInterval("PT10M")));

        $manager->persist($website4);

        $manager->flush();

        $responseLog = new ResponseLog(
            $website2,
            Website::STATUS_OK,
            $time->modify('-50 day'),
            1500,
        );

        $currentTime = new \DateTimeImmutable();
        $downtimeLog1 = new DowntimeLog();
        $downtimeLog1->setWebsite($website);
        $downtimeLog1->setStartTime($currentTime->setTimestamp($currentTime->getTimestamp() - 3000));
        $downtimeLog1->setEndTime($currentTime->setTimestamp($currentTime->getTimestamp() - 2500));
        $downtimeLog1->setInitialError(['Unexpected HTTP status code: 200, expected: 301']);

        $downtimeLog2 = new DowntimeLog();
        $downtimeLog2->setWebsite($website);
        $downtimeLog2->setStartTime($currentTime->setTimestamp($currentTime->getTimestamp() - 200000));
        $downtimeLog2->setEndTime($currentTime->setTimestamp($currentTime->getTimestamp() - 199900));
        $downtimeLog2->setInitialError(['Unexpected HTTP status code: 200, expected: 301']);

        $downtimeLog3 = new DowntimeLog();
        $downtimeLog3->setWebsite($website);
        $downtimeLog3->setStartTime($currentTime->setTimestamp($currentTime->getTimestamp() - 1000));
        $downtimeLog3->setEndTime(null);
        $downtimeLog3->setInitialError(['Unexpected HTTP status code: 200, expected: 301']);

        $manager->persist($responseLog);
        $manager->persist($downtimeLog1);
        $manager->persist($downtimeLog2);
        $manager->persist($downtimeLog3);
        $manager->flush();
    }

    private function createWebsite(
        User $owner,
        string $url = 'https://nonexistent.nonexistent',
        string $method = 'GET',
        int $statusCode = 200,
        int $redirects = 0,
        int $timeout = 10,
        int $frequency = 1,
        bool $enabled = true,
        \DateTimeInterface $lastCheck = null,
    ): Website {
        $website = new Website();
        $website->setUrl($url);
        $website->setRequestMethod($method);
        $website->setExpectedStatusCode($statusCode);
        $website->setMaxRedirects($redirects);
        $website->setTimeout($timeout);
        $website->setFrequency($frequency);
        $website->setEnabled($enabled);
        $website->setOwner($owner);
        $website->setLastCheck($lastCheck);

        return $website;
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }
}
