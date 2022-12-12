<?php
declare(strict_types=1);

namespace App\Service;

use App\Dto\RequestRunnerResponseDto;
use App\Entity\DowntimeLog;
use App\Entity\ResponseLog;
use App\Entity\Website;
use App\Service\Notifier\Notifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\NoPrivateNetworkHttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class RequestsRunner
{
    public function __construct(
        private readonly HttpClientInterface        $client,
        private readonly NoPrivateNetworkHttpClient $noPrivateNetworkHttpClient,
        private readonly EntityManagerInterface     $entityManager,
        private readonly Notifier                   $notifier,
        private readonly bool                       $allowPrivateNetworks,
        /** @var array<ResponseInterface> $responses */
        private array                               $responses = [],
        /** @var array<int, RequestRunnerResponseDto> $responseData */
        private array $responseData = [],
        private readonly int $batchFlushSize = 50,
        private \DateTimeInterface $cronTime = new \DateTimeImmutable(),
    )
    {
        //set seconds to zero due to cron inconsistent startup delay
        $this->cronTime = new \DateTimeImmutable($this->cronTime->format('Y-m-d H:i:00'));
    }

    private function getClient(): HttpClientInterface
    {
        if ($this->allowPrivateNetworks === true) {
            return $this->client;
        } else {
            return $this->noPrivateNetworkHttpClient;
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
                    'user_data' => $website->getId()
                ]);

                $this->updateResponseDto($website->getId(), $website);

            } catch (TransportExceptionInterface $e) {
                $this->updateResponseDto($website->getId(), $website, ['Transport exception'], microtime(true));
            }
        }

        foreach ($this->getClient()->stream($this->responses) as $response => $chunk) {
            try {
                if ($chunk->isTimeout()) {
                    $this->updateResponseDto(
                        $this->getWebsiteIdFromResponse($response),
                        null,
                        ['Timeout'],
                        floatval($response->getInfo('start_time')),
                        $this->calculateResponseTime(floatval($response->getInfo('start_time'))),
                    );

                    $response->cancel();
                } elseif ($chunk->isFirst()) {
                    //prevent exception
                    $response->getStatusCode();
                } elseif ($chunk->isLast()) {
                    //response completed
                    $this->updateResponseDto(
                        $this->getWebsiteIdFromResponse($response),
                        null,
                        [],
                        floatval($response->getInfo('start_time')),
                        intval(round($response->getInfo('total_time') * 1000)),
                        $response->getStatusCode(),
                        $this->getCertExpireDate($response),
                    );
                }
            } catch (TransportExceptionInterface $e) {
                $this->updateResponseDto(
                    $this->getWebsiteIdFromResponse($response),
                    null,
                    ['Stream transport exception'],
                    floatval($response->getInfo('start_time')),
                );
            }
        }

        $currentResponse = 1;
        foreach ($this->responseData as $websiteId => $dto) {
            $this->responseLog($dto);

            //flush in batches for better performance
            if ($currentResponse % $this->batchFlushSize === 0) {
                $this->entityManager->flush();
            }
            $currentResponse++;
        }

        $this->entityManager->flush();
    }

    private function responseLog(RequestRunnerResponseDto $dto): void
    {
        $status = Website::STATUS_OK;

        //check status code if there are no errors
        if (empty($dto->errors)) {
            if ($dto->website && $dto->statusCode != $dto->website->getExpectedStatusCode()) {
                $dto->errors[] = sprintf('Unexpected HTTP status code: %d, expected: %d',
                    $dto->statusCode,
                    $dto->website->getExpectedStatusCode()
                );
            }
        }

        //check certificate info if status code is ok
        if (empty($dto->errors) && $dto->website) {
            $dto->website = $this->updateWebsiteCertExpireTime($dto->website, $dto->certExpireTime);
        }

        if (!empty($dto->errors)) {
            $status = Website::STATUS_ERROR;
        }

        //executes when site goes back up
        if (($dto->website?->getLastStatus() != Website::STATUS_OK) && $status == Website::STATUS_OK) {
            $this->endDowntime($dto);
        }

        //executes when website goes down
        if (($dto->website?->getLastStatus() == Website::STATUS_OK) && $status == Website::STATUS_ERROR) {
            $this->createDowntime($dto);
        }

        $dto->website?->setLastCheck($this->cronTime);
        $dto->website?->setLastStatus($status);
        dump($dto->errors);
        if ($dto->website) {
            if ($status == Website::STATUS_OK) {
                $responseLog = new ResponseLog(
                    $dto->website,
                    $status,
                    $this->cronTime,
                    intval($dto->totalTime),
                );

                $this->entityManager->persist($responseLog);
            }

            $this->entityManager->persist($dto->website);
        }
    }

    private function getWebsiteIdFromResponse(ResponseInterface $response): int
    {
        return intval($response->getInfo('user_data'));
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

    private function calculateResponseTime(float $startTime): float
    {
        return intval(round((microtime(true) - $startTime) * 1000));
    }

    private function createDowntime(RequestRunnerResponseDto $dto): void
    {
        if ($dto->website) {
            $downtimeLog = new DowntimeLog();
            $downtimeLog->setWebsite($dto->website);
            $downtimeLog->setStartTime(new \DateTimeImmutable());
            $downtimeLog->setInitialError($dto->errors);
            $this->entityManager->persist($downtimeLog);

            $message = sprintf("Url: %s \nErrors: %s",
                $dto->website->getUrl(),
                implode("\n", $dto->errors)
            );

            $this->sendNotification($dto->website, 'Website is down', $message);
        }
    }

    private function endDowntime(RequestRunnerResponseDto $dto): void
    {
        $downtimeLog = $dto->website?->getRecentDowntimeLog();

        if ($downtimeLog && $dto->website && $downtimeLog->getEndTime() == null) {
            $datetime = new \DateTimeImmutable();
            $datetime = $datetime->setTimestamp(intval($dto->startTime));

            $downtimeLog->setEndTime($datetime);
            $this->entityManager->persist($downtimeLog);

            $message = sprintf("Url: %s \nIncident start: %s \nIncident end: %s",
                $dto->website->getUrl(),
                $downtimeLog->getStartTime()->format('Y-m-d H:i:s'),
                $datetime->format('Y-m-d H:i:s'),
            );

            // TODO add translation
            $this->sendNotification($dto->website, 'Website is back online', $message);
        }
    }

    private function sendNotification(Website $website, string $subject, string $message): void
    {
        foreach ($website->getNotifierChannels()->getIterator() as $channel) {
            // TODO add translation
            $this->notifier->sendNotification($channel->getType(), $subject, $message, $channel->getOptions());
        }
    }

    private function getResponseDto(int $websiteId): RequestRunnerResponseDto
    {
        if (!isset($this->responseData[$websiteId])){
            $this->responseData[$websiteId] = new RequestRunnerResponseDto();
        }

        return $this->responseData[$websiteId];
    }

    /** @param array<int, string> $errors */
    private function updateResponseDto(
        ?int $websiteId,
        ?Website $website = null,
        array $errors = [],
        ?float $startTime = null,
        ?float $totalTime = null,
        ?int $statusCode = null,
        ?\DateTimeInterface $certExpireTime = null,
    ): void
    {
        if ($websiteId) {
            $dto = $this->getResponseDto($websiteId);

            $dto->website = $website ?? $dto->website;
            $dto->errors = array_merge($dto->errors, $errors);
            $dto->startTime = $startTime ?? $dto->startTime;
            $dto->totalTime = $totalTime ?? $dto->totalTime;
            $dto->statusCode = $statusCode ?? $dto->statusCode;
            $dto->certExpireTime = $certExpireTime ?? $dto->certExpireTime;

            $this->responseData[$websiteId] = $dto;
        }
    }

    private function updateWebsiteCertExpireTime(Website $website, ?\DateTimeInterface $certExpireTime = null): Website
    {
        if ($website->getCertExpiryTime() === null) {
            if ($certExpireTime !== null) {
                $website->setCertExpiryTime($certExpireTime);
            }
        } elseif ($certExpireTime !== null && $website->getCertExpiryTime() != $certExpireTime) {
            $message = sprintf("Previous expire date: %s\nNew expire date: %s",
                $website->getCertExpiryTime()->format('Y-m-d H:i:s'),
                $certExpireTime->format('Y-m-d H:i:s'),
            );
            $this->sendNotification($website, 'Website certificate changed', $message);
            $website->setCertExpiryTime($certExpireTime);
        }

        return $website;
    }

    /** @return array<int, RequestRunnerResponseDto> */
    public function getResponseData(): array
    {
        return $this->responseData;
    }
}