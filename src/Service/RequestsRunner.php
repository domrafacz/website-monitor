<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\RequestRunnerResponseDto;
use App\Entity\Website;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class RequestsRunner
{
    public function __construct(
        private readonly HttpClientInterface        $client,
        private readonly EntityManagerInterface     $entityManager,
        private readonly WebsiteManager $websiteManager,
        private readonly RequestsRunnerResponseParser $responseParser,
        /** @var array<ResponseInterface> $responses */
        private array                               $responses = [],
        /** @var array<int, RequestRunnerResponseDto> $responseData */
        private array $responseData = [],
        private readonly int $batchFlushSize = 50,
        private \DateTimeImmutable $cronTime = new \DateTimeImmutable(),
    ) {
        //set seconds to zero due to cron inconsistent startup delay
        $this->cronTime = new \DateTimeImmutable($this->cronTime->format('Y-m-d H:i:00'));
    }

    /** @param array<Website> $websites  */
    public function run(array $websites): void
    {
        foreach ($websites as $website) {
            try {
                $this->responses[] = $this->client->request($website->getRequestMethod(), $website->getUrl(), [
                    'timeout' => $website->getTimeout(),
                    'max_redirects' => $website->getMaxRedirects(),
                    'capture_peer_cert_chain' => true,
                    'user_data' => $website->getId()
                ]);

                $this->updateResponseDto($website->getId(), $website);
            } catch (TransportExceptionInterface $e) {
                $this->updateResponseDto($website->getId(), $website, ['request_runner_transport_exception'], microtime(true));
            }
        }

        $this->streamResponses();
        $this->parseResponses();
    }

    private function getResponseDto(int $websiteId): RequestRunnerResponseDto
    {
        if (!isset($this->responseData[$websiteId])) {
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
    ): void {
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

    /** @return array<int, RequestRunnerResponseDto> */
    public function getResponseData(): array
    {
        return $this->responseData;
    }

    private function createSuccessfulResponse(ResponseInterface $response): void
    {
        $this->updateResponseDto(
            websiteId: $this->responseParser->getWebsiteId($response),
            startTime: floatval($response->getInfo('start_time')),
            totalTime: intval(round($response->getInfo('total_time') * 1000)),
            statusCode: $response->getStatusCode(),
            certExpireTime: $this->responseParser->getCertExpireDate($response),
        );
    }

    private function createUnsuccessfulResponse(ResponseInterface $response, string $error): void
    {
        $this->updateResponseDto(
            $this->responseParser->getWebsiteId($response),
            null,
            [$error],
            floatval($response->getInfo('start_time')),
        );
    }

    private function streamResponses(): void
    {
        foreach ($this->client->stream($this->responses) as $response => $chunk) {
            try {
                if ($chunk->isTimeout()) {
                    $this->createUnsuccessfulResponse($response, 'request_runner_timeout');
                    $response->cancel();
                } elseif ($chunk->isFirst()) {
                    //prevent exception
                    $response->getStatusCode();
                } elseif ($chunk->isLast()) {
                    //response completed
                    $this->createSuccessfulResponse($response);
                }
            } catch (TransportExceptionInterface $e) {
                $this->createUnsuccessfulResponse($response, 'request_runner_transport_exception');
            }
        }
    }

    private function parseResponses(): void
    {
        $currentResponse = 1;
        foreach ($this->responseData as $websiteId => $dto) {
            $this->websiteManager->addResponseLog($this->responseParser->parse($dto), $this->cronTime);

            //flush in batches for better performance
            if ($currentResponse % $this->batchFlushSize === 0) {
                $this->entityManager->flush();
            }
            $currentResponse++;
        }

        $this->entityManager->flush();
    }
}
