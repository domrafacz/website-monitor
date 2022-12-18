<?php

declare(strict_types=1);

namespace App\Factory;

use App\Dto\WebsiteDto;
use App\Entity\Website;

class WebsiteFactory
{
    public function createDto(Website $website): WebsiteDto
    {
        $dto = new WebsiteDto();

        $dto->url = $website->getUrl();
        $dto->requestMethod = $website->getRequestMethod();
        $dto->maxRedirects = $website->getMaxRedirects();
        $dto->timeout = $website->getTimeout();
        $dto->frequency = $website->getFrequency();
        $dto->enabled = $website->isEnabled();
        $dto->expectedStatusCode = $website->getExpectedStatusCode();

        return $dto;
    }

    public function createFromDto(WebsiteDto $dto): Website
    {
        $website = new Website();

        $website->setUrl($dto->url);
        $website->setRequestMethod($dto->requestMethod);
        $website->setMaxRedirects($dto->maxRedirects);
        $website->setTimeout($dto->timeout);
        $website->setFrequency($dto->frequency);
        $website->setEnabled($dto->enabled);
        $website->setExpectedStatusCode($dto->expectedStatusCode);

        return $website;
    }
}
