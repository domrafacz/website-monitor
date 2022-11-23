<?php

namespace App\Tests\Unit\Service;

use App\Entity\Website;
use App\Service\RequestsRunner;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\NoPrivateNetworkHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class RequestsRunnerTest extends KernelTestCase
{
    public function testRun(): void
    {
        $client = new MockHttpClient();
        $client2 = new NoPrivateNetworkHttpClient($client);
        $entityManager = $this->createMock(EntityManagerInterface::class);


        $website = new Website();
        $website->setUrl('https://nonexistent.nonexistent');
        $website->setRequestMethod('GET');
        $website->setMaxRedirects(20);
        $website->setTimeout(0);
        $website->setFrequency(1);
        $website->setEnabled(true);
        $website->setLastStatus(Website::STATUS_ERROR);

        $website2 = new Website();
        $website2->setUrl('https://nonexistent.nonexistent');
        $website2->setRequestMethod('GET');
        $website2->setMaxRedirects(20);
        $website2->setTimeout(0);
        $website2->setFrequency(1);
        $website2->setEnabled(true);
        $website2->setLastStatus(Website::STATUS_ERROR);

        $requestRunner = new RequestsRunner(
            $client,
            $client2,
            $entityManager,
            true,
        );

        $requestRunner->run([$website]);

        $this->assertEquals(Website::STATUS_OK, $website->getLastStatus());
    }
}