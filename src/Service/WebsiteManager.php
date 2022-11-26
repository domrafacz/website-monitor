<?php
declare(strict_types=1);

namespace App\Service;

use App\Dto\WebsiteDto;
use App\Entity\User;
use App\Entity\Website;
use App\Repository\WebsiteRepository;

class WebsiteManager
{
    public function __construct(
        private readonly WebsiteRepository $websiteRepository,
    ) {}

    public function edit(Website $website, WebsiteDto $dto): void
    {
        $website->setUrl($dto->url);
        $website->setRequestMethod($dto->requestMethod);
        $website->setMaxRedirects($dto->maxRedirects);
        $website->setTimeout($dto->timeout);
        $website->setFrequency($dto->frequency);
        $website->setEnabled($dto->enabled);
        $website->setExpectedStatusCode($dto->expectedStatusCode);

        $this->websiteRepository->save($website, true);
    }

    public function addOwner(Website $website, User $user, bool $flush): void
    {
        $user->addWebsite($website);
        $this->websiteRepository->save($website, $flush);
    }

    public function delete(Website $website, bool $flush = true): void
    {
        $this->websiteRepository->remove($website, $flush);
    }
}