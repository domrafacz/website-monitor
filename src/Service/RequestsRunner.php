<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\DowntimeLog;
use App\Entity\ResponseLog;
use App\Entity\Website;
use App\Service\Notifier\Notifier;
use Doctrine\Common\Collections\Criteria;
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
        private readonly Notifier $notifier,
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
        $datetime = new \DateTimeImmutable();
        $datetime = $datetime->setTimestamp(intval($response->getInfo('start_time')));
        $certExpireTime = $this->getCertExpireDate($response);

        //checking if cert has been changed
        if ($website->getCertExpiryTime() !== null && $certExpireTime !== null && $website->getCertExpiryTime() != $certExpireTime){
            $message = sprintf("Previous expire date: %s\nNew expire date: %s",
                $website->getCertExpiryTime()->format('Y-m-d H:i:s'),
                $certExpireTime->format('Y-m-d H:i:s'),
            );
            $this->sendNotification($website, 'Website certificate changed', $message);
            $website->setCertExpiryTime($certExpireTime);
        } elseif ($website->getCertExpiryTime() === null && $certExpireTime !== null) {
            $website->setCertExpiryTime($certExpireTime);
        }

        //checking status code
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

        //errors handling
        if (!empty($errors)) {
            $status = Website::STATUS_ERROR;
        }

        //executes when website goes down
        if (($website->getLastStatus() != Website::STATUS_OK) && $status == Website::STATUS_OK) {
            $downtimeLog = $this->getRecentDowntimeLog($website);

            if ($downtimeLog && $downtimeLog->getEndTime() == null) {
                $downtimeLog->setEndTime($datetime);
                $this->entityManager->persist($downtimeLog);
                $message = sprintf("Url: %s \nIncident start: %s \nIncident end: %s",
                    $website->getUrl(),
                    $downtimeLog->getStartTime()->format('Y-m-d H:i:s'),
                    $datetime->format('Y-m-d H:i:s'),
                );
                $this->sendNotification($website, 'Website is back online', $message);
            }
        }

        //executes when site goes back up
        if (($website->getLastStatus() == Website::STATUS_OK) && $status == Website::STATUS_ERROR) {
            $this->createDowntimeLog($website, $errors);
            $message = sprintf("Url: %s \nErrors: %s",
                $website->getUrl(),
                implode("\n", $errors)
            );
            $this->sendNotification($website, 'Website is down', $message);
        }

        $website->setLastCheck($datetime);
        $website->setLastStatus($status);

        $responseLog = new ResponseLog(
            $website,
            $status,
            $datetime,
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

    /** @return array{}|array<int, string> */
    private function getErrors(?int $websiteId): array
    {
        $errors = [];

        if (!$websiteId) {
            return $errors;
        }

        foreach ($this->errors as $key => $error) {
            if ($error['website_id'] == $websiteId) {
                if (is_string($error['error'])) {
                    $errors[] = $error['error'];
                }
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

    /** @param array<int, string> $errors */
    private function createDowntimeLog(Website $website, array $errors): void
    {
        $downtimeLog = new DowntimeLog();
        $downtimeLog->setWebsite($website);
        $downtimeLog->setStartTime(new \DateTimeImmutable());
        $downtimeLog->setInitialError($errors);

        $this->entityManager->persist($downtimeLog);
    }

    private function getRecentDowntimeLog(Website $website): ?DowntimeLog
    {
        $criteria = Criteria::create()
            ->orderBy(array('id' => Criteria::DESC));

        $downtimeLog = $website->getDowntimeLogs()->matching($criteria)->first();

        if ($downtimeLog instanceof DowntimeLog) {
            return $downtimeLog;
        } else {
            return null;
        }
    }

    private function sendNotification(Website $website, string $subject, string $message): void
    {
        foreach ($website->getNotifierChannels()->getIterator() as $channel) {
            // TODO add translation
            $this->notifier->sendNotification($channel->getType(), $subject, $message, $channel->getOptions());
        }
    }
}