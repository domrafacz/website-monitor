<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\RequestsRunner;
use App\Service\RequestsRunnerResponseParser;
use App\Service\WebsiteManager;
use App\Tests\Unit\Traits\WebsiteTrait;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\NoPrivateNetworkHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

class RequestsRunnerTest extends TestCase
{
    use WebsiteTrait;

    private function createRequestRunner(
        callable|iterable|ResponseInterface $responseFactory = null,
        bool $allowPrivateNetworks = false,
        int $batchFlushSize = 50,
    ): RequestsRunner {
        if ($responseFactory != null) {
            $client = new MockHttpClient($responseFactory);
        } else {
            $client = new MockHttpClient();
        }

        if ($allowPrivateNetworks === false) {
            $client = new NoPrivateNetworkHttpClient($client);
        }


        return  new RequestsRunner(
            $client,
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(WebsiteManager::class),
            new RequestsRunnerResponseParser(),
            batchFlushSize: $batchFlushSize,
        );
    }

    public function testNoPrivateNetworkHttpclient(): void
    {
        $requestRunner = $this->createRequestRunner();

        $requestRunner->run([]);

        $this->assertEquals([], $requestRunner->getResponseData());
    }

    public function testTransportExceptionOnRequestCreation(): void
    {
        $errorMessage = 'request_runner_transport_exception';
        $requestRunner = $this->createRequestRunner(
            responseFactory: function () {
            },
            allowPrivateNetworks: true,
        );

        $website = $this->createWebsite(10, 'https://google.com', 'GET');

        $requestRunner->run([$website], true);

        $this->assertEquals($errorMessage, $requestRunner->getResponseData()[$website->getId()]->errors[0]);
        $this->assertEquals(1, $requestRunner->getStatistics()['failed']);
    }

    public function testRequestTimeout(): void
    {
        $mockResponse = new MockResponse(['','']);
        $errorMessage = 'request_runner_timeout';

        $requestRunner = $this->createRequestRunner(
            responseFactory: [$mockResponse],
            allowPrivateNetworks: true,
        );

        $website = $this->createWebsite(10, 'https://google.com', 'GET');

        $requestRunner->run([$website]);

        $this->assertEquals($errorMessage, $requestRunner->getResponseData()[$website->getId()]->errors[0]);
    }

    public function testResponseStreamTransportException()
    {
        $mockResponse = new MockResponse('...', ['error' => 'test_stream_exception']);
        $errorMessage = 'request_runner_transport_exception';

        $requestRunner = $this->createRequestRunner(
            responseFactory: [$mockResponse],
            allowPrivateNetworks: true,
        );

        $website = $this->createWebsite(10, 'https://google.com', 'GET');

        $requestRunner->run([$website]);

        $this->assertEquals($errorMessage, $requestRunner->getResponseData()[$website->getId()]->errors[0]);
    }

    public function testResponseUnexpectedStatusCode(): void
    {
        $mockResponse = new MockResponse('...', ['http_code' => '404']);
        $errorMessage = [
            'request_runner_unexpected_http_code_simple',
            '404',
        ];

        $requestRunner = $this->createRequestRunner(
            responseFactory: [$mockResponse],
            allowPrivateNetworks: true,
        );

        $website = $this->createWebsite(10, 'https://google.com', 'GET');
        $requestRunner->run([$website]);

        $this->assertEquals($errorMessage[0], $requestRunner->getResponseData()[$website->getId()]->errors[0]);
        $this->assertEquals($errorMessage[1], $requestRunner->getResponseData()[$website->getId()]->errors[1]);
    }

    public function testFlushBatch(): void
    {
        $requestRunner = $this->createRequestRunner(
            allowPrivateNetworks: true,
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
