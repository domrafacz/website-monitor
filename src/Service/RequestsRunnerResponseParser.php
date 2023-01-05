<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\RequestRunnerResponseDto;
use App\Entity\Website;
use Symfony\Contracts\HttpClient\ResponseInterface;

class RequestsRunnerResponseParser
{
    public function parse(RequestRunnerResponseDto $responseDto): RequestRunnerResponseDto
    {
        if (!$responseDto->website) {
            $responseDto->errors[] = 'website is invalid';
        }

        //check status code only if there are no errors
        if (empty($responseDto->errors) && $responseDto->statusCode != $responseDto->website?->getExpectedStatusCode()) {
            $responseDto->errors[] = 'request_runner_unexpected_http_code_simple';
            $responseDto->errors[] = strval($responseDto->statusCode);
        }

        if (!empty($responseDto->errors)) {
            $responseDto->status = Website::STATUS_ERROR;
        }

        return $responseDto;
    }

    public function getWebsiteId(ResponseInterface $response): int
    {
        return intval($response->getInfo('user_data'));
    }

    public function getCertExpireDate(ResponseInterface $response): ?\DateTimeInterface
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
}
