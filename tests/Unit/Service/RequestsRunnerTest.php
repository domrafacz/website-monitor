<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\DowntimeLog;
use App\Entity\NotifierChannel;
use App\Entity\User;
use App\Entity\Website;
use App\Service\Notifier\Notifier;
use App\Service\RequestsRunner;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\NoPrivateNetworkHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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
    ): Website {
        $user = new class () extends User {
            public function getId(): int
            {
                return 1;
            }
        };

        $website = new class ($id) extends Website {
            public function __construct(int $id)
            {
                parent::__construct();
                $this->id = $id;
            }

            public function getId(): int
            {
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
        $website->setOwner($user);

        $notifierChannel = $this->createMock(NotifierChannel::class);
        $website->addNotifierChannel($notifierChannel);

        return $website;
    }

    private function createRequestRunner(
        callable|iterable|ResponseInterface $responseFactory = null,
        bool $allowPrivateNetworks = false,
        int $batchFlushSize = 50,
        string $translatorMessage = '',
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
            $this->createMock(Notifier::class),
            $this->createTranslator($translatorMessage),
            batchFlushSize: $batchFlushSize,
        );
    }

    private function createTranslator(string $translatorMessage): TranslatorInterface
    {
        return new class ($translatorMessage) implements TranslatorInterface {
            private string $translatorMessage;

            public function __construct($translatorMessage)
            {
                $this->translatorMessage = $translatorMessage;
            }
            public function getLocale(): string
            {
                return 'en';
            }

            public function trans(string $id, array $parameters = [], string $domain = null, string $locale = null): string
            {
                return $this->translatorMessage;
            }
        };
    }

    public function testNoPrivateNetworkHttpclient(): void
    {
        $requestRunner = $this->createRequestRunner();

        $requestRunner->run([]);

        $this->assertEquals([], $requestRunner->getResponseData());
    }

    public function testTransportExceptionOnRequestCreation(): void
    {
        $errorMessage = 'Transport exception';
        $requestRunner = $this->createRequestRunner(
            responseFactory: function () {
            },
            allowPrivateNetworks: true,
            translatorMessage: $errorMessage
        );

        $website = $this->createWebsite(10, 'https://google.com', 'GET');

        $requestRunner->run([$website]);

        $this->assertEquals($errorMessage, $requestRunner->getResponseData()[$website->getId()]->errors[0]);
    }

    public function testRequestTimeout(): void
    {
        $mockResponse = new MockResponse(['','']);
        $errorMessage = 'Timeout';

        $requestRunner = $this->createRequestRunner(
            responseFactory: [$mockResponse],
            allowPrivateNetworks: true,
            translatorMessage: $errorMessage
        );

        $website = $this->createWebsite(10, 'https://google.com', 'GET');

        $requestRunner->run([$website]);

        $this->assertEquals($errorMessage, $requestRunner->getResponseData()[$website->getId()]->errors[0]);
    }

    public function testResponseStreamTransportException()
    {
        $mockResponse = new MockResponse('...', ['error' => 'test_stream_exception']);
        $errorMessage = 'Stream transport exception';

        $requestRunner = $this->createRequestRunner(
            responseFactory: [$mockResponse],
            allowPrivateNetworks: true,
            translatorMessage: $errorMessage
        );

        $website = $this->createWebsite(10, 'https://google.com', 'GET');

        $requestRunner->run([$website]);

        $this->assertEquals($errorMessage, $requestRunner->getResponseData()[$website->getId()]->errors[0]);
    }

    public function testResponseUnexpectedStatusCode(): void
    {
        $mockResponse = new MockResponse('...', ['http_code' => '404']);
        $errorMessage = 'Unexpected HTTP status code: 404, expected: 200';

        $requestRunner = $this->createRequestRunner(
            responseFactory: [$mockResponse],
            allowPrivateNetworks: true,
            translatorMessage: $errorMessage
        );

        $website = $this->createWebsite(10, 'https://google.com', 'GET');
        $requestRunner->run([$website]);

        $this->assertEquals($errorMessage, $requestRunner->getResponseData()[$website->getId()]->errors[0]);
    }

    public function testSetCertExpireTime(): void
    {
        $datetime = new \DateTimeImmutable();
        $datetime = $datetime->setTimestamp($datetime->getTimestamp() + 2592000);

        $mockResponse = new MockResponse('...', ['certinfo' => [0 => ['Expire date' => $datetime->format('M j H:i:s Y').' GMT']]]);

        $requestRunner = $this->createRequestRunner(
            responseFactory: [$mockResponse],
            allowPrivateNetworks: true,
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

        $requestRunner = $this->createRequestRunner(
            responseFactory: [$mockResponse],
            allowPrivateNetworks: true,
        );

        $website = $this->createWebsite(10, 'https://google.com', 'GET');
        $website->setCertExpiryTime($datetime->setTimestamp($datetime->getTimestamp() - 5000));
        $requestRunner->run([$website]);

        $this->assertEquals($datetime->getTimestamp(), $requestRunner->getResponseData()[$website->getId()]->website?->getCertExpiryTime()->getTimestamp());
    }

    public function testEndDowntime(): void
    {
        $datetime = new \DateTimeImmutable();

        $requestRunner = $this->createRequestRunner(
            allowPrivateNetworks: true,
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
