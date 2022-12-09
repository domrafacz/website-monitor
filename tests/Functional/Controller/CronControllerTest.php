<?php
declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Repository\WebsiteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CronControllerTest extends WebTestCase
{
    public function testRunRequests(): void
    {
        $client = static::createClient();
        $websiteRepository = static::getContainer()->get(WebsiteRepository::class);
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $websites = $websiteRepository->findAll();

        foreach ($websites as $website) {
            $website->setTimeout(1);
            $entityManager->persist($website);
        }

        $entityManager->flush();

        $client->request('GET', '/cron/run-requests');
        $this->assertResponseIsSuccessful();
    }

}