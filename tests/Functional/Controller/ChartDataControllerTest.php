<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ChartDataControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
    }

    protected function tearDown(): void
    {
        unset($this->userRepository);
        unset($this->client);
        parent::tearDown();
    }

    public function testGetChartDataReturnsJson(): void
    {
        $testUser = $this->userRepository->findOneByUsername('test1@test.com');
        $this->client->loginUser($testUser);

        $website = $testUser->getWebsites()->first();

        $this->client->request('GET', '/api/website/' . $website->getId() . '/chart-data?period=24h');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('uptime', $responseData);
        $this->assertArrayHasKey('latency', $responseData);
        $this->assertArrayHasKey('period', $responseData);
        $this->assertEquals('24h', $responseData['period']);
    }

    public function testGetChartDataWithDifferentPeriods(): void
    {
        $testUser = $this->userRepository->findOneByUsername('test1@test.com');
        $this->client->loginUser($testUser);

        $website = $testUser->getWebsites()->first();

        $periods = ['1h', '24h', '7d', '30d', '3m', '6m', '1y'];

        foreach ($periods as $period) {
            $this->client->request('GET', '/api/website/' . $website->getId() . '/chart-data?period=' . $period);

            $this->assertResponseIsSuccessful();

            $responseData = json_decode($this->client->getResponse()->getContent(), true);
            $this->assertEquals($period, $responseData['period']);
        }
    }

    public function testGetChartDataInvalidPeriod(): void
    {
        $testUser = $this->userRepository->findOneByUsername('test1@test.com');
        $this->client->loginUser($testUser);

        $website = $testUser->getWebsites()->first();

        $this->client->request('GET', '/api/website/' . $website->getId() . '/chart-data?period=invalid');

        $this->assertResponseStatusCodeSame(400);

        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $responseData);
    }

    public function testGetChartDataUnauthorized(): void
    {
        $testUser = $this->userRepository->findOneByUsername('test1@test.com');
        $website = $testUser->getWebsites()->first();

        // Not logged in
        $this->client->request('GET', '/api/website/' . $website->getId() . '/chart-data?period=24h');

        $this->assertResponseRedirects();
    }

    public function testGetChartDataOtherUserWebsite(): void
    {
        $testUser = $this->userRepository->findOneByUsername('test1@test.com');
        $this->client->loginUser($testUser);

        $secondUser = $this->userRepository->findOneByUsername('test11@test.com');
        $otherWebsite = $secondUser->getWebsites()->first();

        $this->client->request('GET', '/api/website/' . $otherWebsite->getId() . '/chart-data?period=24h');

        $this->assertResponseStatusCodeSame(404);
    }
}
