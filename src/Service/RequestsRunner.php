<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\ResponseLog;
use App\Entity\Website;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\NoPrivateNetworkHttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class RequestsRunner
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly NoPrivateNetworkHttpClient $privateNetworkHttpClient,
        private readonly EntityManagerInterface $entityManager,
        private readonly bool $allowPrivateNetworks,
        /** @var array<ResponseInterface> $responses */
        private array $responses = [],
        /** @var array<array<string, int|string>> $errors */
        private array $errors = [],
        /** @var array<int, int> $responsesTime */
        private array $responsesTime = [],
    ) {}

    private function getClient(): HttpClientInterface
    {
        if ($this->allowPrivateNetworks === true) {
            return $this->client;
        } else {
            return $this->privateNetworkHttpClient;
        }
    }

    /** @param array<Website> $websites  */
    public function run(array $websites): void
    {
        foreach ($websites as $website) {
            try {
                $this->responses[] = $this->getClient()->request($website->getRequestMethod(), $website->getUrl(), [
                    'timeout' => $website->getTimeout(),
                    'max_redirects' => $website->getMaxRedirects(),
                    'capture_peer_cert_chain' => true,
                    'user_data' => [
                        'website' => $website,
                    ]
                ]);

            } catch (TransportExceptionInterface $e) {
                $this->addError($website->getId(), $e->getMessage());
                $this->addResponseTime($website->getId(), -1);
            }
        }

        foreach ($this->getClient()->stream($this->responses) as $response => $chunk) {
            try {
                if ($chunk->isTimeout()) {
                    $this->addError($this->getWebsite($response)->getId(), 'timeout');
                    $this->addResponseTime($this->getWebsite($response)->getId(), floatval($response->getInfo('start_time')));
                    $response->cancel();
                } elseif ($chunk->isFirst()) {
                    //prevent exception
                    $response->getStatusCode();
                } elseif ($chunk->isLast()) {
                    //request finished
                    $this->addResponseTime($this->getWebsite($response)->getId(), floatval($response->getInfo('start_time')));
                }
            } catch (TransportExceptionInterface $e) {
                $this->addError($this->getWebsite($response)->getId(), $e->getMessage());
                $this->addResponseTime($this->getWebsite($response)->getId(), -1);
            }
        }

        $currentResponse = 1;
        foreach ($this->responses as $response) {
            $this->responseLog($response);

            //flush in batches for better performance
            if ($currentResponse % 50 === 0) {
                $this->entityManager->flush();
            }
            $currentResponse++;
        }

        $this->entityManager->flush();
    }

    private function responseLog(ResponseInterface $response): void
    {
        $status = Website::STATUS_OK;
        $website = $this->getWebsite($response);
        $errors = $this->getErrors($website->getId());

        if ($website->getCertExpiryTime() != $this->getCertExpireDate($response)){
            //TODO add notification
            $website->setCertExpiryTime($this->getCertExpireDate($response));
        }

        try {
            if ($response->getStatusCode() != $website->getExpectedStatusCode()) {
                $status = Website::STATUS_ERROR;
                // TODO add translation
                $errors[] = sprintf('Unexpected HTTP status code: %d, expected: %d',
                    $response->getStatusCode(),
                    $website->getExpectedStatusCode()
                );
            }
        } catch (TransportExceptionInterface $e) {
        }

        if (!empty($errors)) {
            // TODO better error handling
            $status = Website::STATUS_ERROR;
        }

        $website->setLastCheck(new \DateTimeImmutable());
        $website->setLastStatus($status);

        if (($website->getLastStatus() != Website::STATUS_OK) && $status == Website::STATUS_OK) {
            // TODO send notification website is back up
        }

        if (($website->getLastStatus() == Website::STATUS_OK) && $status == Website::STATUS_ERROR) {
            // TODO create downtime entity
        }

        $responseLog = new ResponseLog(
            $website,
            $status,
            new \DateTimeImmutable(),
            $this->getResponseTime($website->getId())
        );

        $this->entityManager->persist($website);
        $this->entityManager->persist($responseLog);
    }

    private function getWebsite(ResponseInterface $response): Website
    {
        /** @var array<string, Website> $userData */
        $userData = $response->getInfo('user_data');

        return $userData['website'];
    }

    private function getCertExpireDate(ResponseInterface $response): ?\DateTimeInterface
    {
        /** @var array<int, array<string>>|null $certInfo */
        $certInfo = $response->getInfo('certinfo');


        if ($certInfo && isset($certInfo[0]['Expire date'])) {
            $time = strtotime($certInfo[0]['Expire date']);

            if ($time) {
                $date = new \DateTimeImmutable();
                return $date->setTimestamp($time);
            }
        }

        return null;
    }

    /** @return array{}|array<int, int|string> */
    private function getErrors(?int $websiteId): array
    {
        $errors = [];

        if (!$websiteId) {
            return $errors;
        }

        foreach ($this->errors as $key => $error) {
            if ($error['website_id'] == $websiteId) {
                $errors[] = $error['error'];
            }
        }

        return $errors;
    }

    private function addError(?int $websiteId, string $message): void
    {
        if (!$websiteId) {
            return;
        }

        $this->errors[] = ['website_id' => $websiteId, 'error' => $message];
    }

    private function addResponseTime(?int $websiteId, float $startTime): void
    {
        if (!$websiteId) {
            return;
        }

        //$startTime = -1 when something went wrong during request, like dns error
        if ($startTime == -1) {
            $this->responsesTime[$websiteId] = intval($startTime);
        } else {
            $this->responsesTime[$websiteId] = intval(round((microtime(true) - $startTime) * 1000));
        }
    }

    private function getResponseTime(?int $websiteId): int
    {
        if (!$websiteId) {
            return 0;
        }

        return $this->responsesTime[$websiteId] ?? 0;
    }
}