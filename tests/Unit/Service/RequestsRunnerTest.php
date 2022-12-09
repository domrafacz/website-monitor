<?php

namespace App\Tests\Unit\Service;

use App\Entity\DowntimeLog;
use App\Entity\NotifierChannel;
use App\Entity\Website;
use App\Service\Notifier\Notifier;
use App\Service\RequestsRunner;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\NoPrivateNetworkHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class RequestsRunnerTest extends KernelTestCase
{
    private function createWebsite(
        int $id,
        string $url,
        string $method,
        int $maxRedirects = 20,
        int $timeout = 30,
        int $frequency = 1,
        bool $enabled = true,
        int $lastStatus = Website::STATUS_OK,
        int $statusCode = 200
    ): Website
    {
        $website = new class($id) extends Website {
            public function __construct(int $id)
            {
                parent::__construct();
                $this->id = $id;
            }

            public function getId(): int {
                return $this->id;
            }
        };

        $website->setUrl('https://nonexistent.nonexistent');
        $website->setRequestMethod('GET');
        $website->setMaxRedirects($maxRedirects);
        $website->setTimeout($timeout);
        $website->setFrequency($frequency);
        $website->setEnabled($enabled);
        $website->setLastStatus($lastStatus);
        $website->setExpectedStatusCode($statusCode);

        $notifierChannel = $this->createMock(NotifierChannel::class);
        $website->addNotifierChannel($notifierChannel);

        return $website;
    }
    public function testNoPrivateNetworkHttpclient(): void
    {
        $client = new MockHttpClient();
        $client2 = new NoPrivateNetworkHttpClient($client);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $notifier = $this->createMock(Notifier::class);

        $requestRunner = new RequestsRunner(
            $client,
            $client2,
            $entityManager,
            $notifier,
            false,
        );

        $requestRunner->run([]);

        $this->assertEquals([], $requestRunner->getResponseData());
    }

    public function testTransportExceptionOnRequestCreation(): void
    {
        $client = new MockHttpClient(function(){ });
        $client2 = new NoPrivateNetworkHttpClient($client);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $notifier = $this->createMock(Notifier::class);

        $website = $this->createWebsite(10, 'https://google.com', 'GET');

        $requestRunner = new RequestsRunner(
            $client,
            $client2,
            $entityManager,
            $notifier,
            true,
        );

        $requestRunner->run([$website]);

        $this->assertEquals('Transport exception', $requestRunner->getResponseData()[$website->getId()]->errors[0]);
    }

    public function testRequestTimeout(): void
    {
        $mockResponse = new MockResponse(['','']);
        $client = new MockHttpClient([$mockResponse]);
        $client2 = new NoPrivateNetworkHttpClient($client);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $notifier = $this->createMock(Notifier::class);

        $requestRunner = new RequestsRunner(
            $client,
            $client2,
            $entityManager,
            $notifier,
            true,
        );

        $website = $this->createWebsite(10, 'https://google.com', 'GET');

        $requestRunner->run([$website]);

        $this->assertEquals('Timeout', $requestRunner->getResponseData()[$website->getId()]->errors[0]);
    }

    public function testResponseStreamTransportException()
    {
        $mockResponse = new MockResponse('...', ['error' => 'test_stream_exception']);
        $client = new MockHttpClient([$mockResponse]);
        $client2 = new NoPrivateNetworkHttpClient($client);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $notifier = $this->createMock(Notifier::class);

        $requestRunner = new RequestsRunner(
            $client,
            $client2,
            $entityManager,
            $notifier,
            true,
        );

        $website = $this->createWebsite(10, 'https://google.com', 'GET');

        $requestRunner->run([$website]);

        $this->assertEquals('Stream transport exception', $requestRunner->getResponseData()[$website->getId()]->errors[0]);
    }

    public function testResponseUnexpectedStatusCode(): void
    {
        $mockResponse = new MockResponse('...', ['http_code' => '404']);
        $client = new MockHttpClient([$mockResponse]);
        $client2 = new NoPrivateNetworkHttpClient($client);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $notifier = $this->createMock(Notifier::class);

        $requestRunner = new RequestsRunner(
            $client,
            $client2,
            $entityManager,
            $notifier,
            true,
        );

        $website = $this->createWebsite(10, 'https://google.com', 'GET');
        $requestRunner->run([$website]);

        $this->assertEquals('Unexpected HTTP status code: 404, expected: 200', $requestRunner->getResponseData()[$website->getId()]->errors[0]);
    }

    public function testSetCertExpireTime(): void
    {
        $datetime = new \DateTimeImmutable();
        $datetime = $datetime->setTimestamp($datetime->getTimestamp() + 2592000);

        $mockResponse = new MockResponse('...', ['certinfo' => [0 => ['Expire date' => $datetime->format('M j H:i:s Y').' GMT']]]);
        $client = new MockHttpClient([$mockResponse]);
        $client2 = new NoPrivateNetworkHttpClient($client);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $notifier = $this->createMock(Notifier::class);

        $requestRunner = new RequestsRunner(
            $client,
            $client2,
            $entityManager,
            $notifier,
            true,
        );

        $website = $this->createWebsite(10, 'https://google.com', 'GET');
        $requestRunner->run([$website]);

        $this->assertEquals($datetime->getTimestamp(), $requestRunner->getResponseData()[$website->getId()]->website?->getCertExpiryTime()->getTimestamp());
    }

    public function testUpdateCertExpireTime(): void
    {
        $datetime = new \DateTimeImmutable();
        $datetime = $datetime->setTimestamp($datetime->getTimestamp() + 2592000);

        $mockResponse = new MockResponse('...', ['certinfo' => [0 => ['Expire date' => $datetime->format('M j H:i:s Y').' GMT']]]);
        $client = new MockHttpClient([$mockResponse]);
        $client2 = new NoPrivateNetworkHttpClient($client);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $notifier = $this->createMock(Notifier::class);

        $requestRunner = new RequestsRunner(
            $client,
            $client2,
            $entityManager,
            $notifier,
            true,
        );

        $website = $this->createWebsite(10, 'https://google.com', 'GET');
        $website->setCertExpiryTime($datetime->setTimestamp($datetime->getTimestamp() - 5000));
        $requestRunner->run([$website]);

        $this->assertEquals($datetime->getTimestamp(), $requestRunner->getResponseData()[$website->getId()]->website?->getCertExpiryTime()->getTimestamp());
    }

    public function testEndDowntime(): void
    {
        $datetime = new \DateTimeImmutable();
        $client = new MockHttpClient();
        $client2 = new NoPrivateNetworkHttpClient($client);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $notifier = $this->createMock(Notifier::class);

        $requestRunner = new RequestsRunner(
            $client,
            $client2,
            $entityManager,
            $notifier,
            true,
        );

        $downtimeLog = new DowntimeLog();
        $downtimeLog->setStartTime($datetime->setTimestamp($datetime->getTimestamp() - 2000));
        $downtimeLog->setInitialError(['Timeout']);

        $website = $this->createWebsite(10, 'https://google.com', 'GET', lastStatus: Website::STATUS_ERROR);
        $website->addDowntimeLog($downtimeLog);

        $requestRunner->run([$website]);

        $this->assertNotNull($requestRunner->getResponseData()[$website->getId()]->website?->getDowntimeLogs()->first()->getEndTime());
    }

    public function testFlushBatch(): void
    {
        $client = new MockHttpClient();
        $client2 = new NoPrivateNetworkHttpClient($client);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $notifier = $this->createMock(Notifier::class);

        $requestRunner = new RequestsRunner(
            $client,
            $client2,
            $entityManager,
            $notifier,
            true,
            batchFlushSize: 2,
        );

        $requestRunner->run([
            $this->createWebsite(10, 'https://google.com', 'GET'),
            $this->createWebsite(11, 'https://google.com', 'GET'),
            $this->createWebsite(12, 'https://google.com', 'GET'),
        ]);

        $this->assertCount(3, $requestRunner->getResponseData());
    }
}