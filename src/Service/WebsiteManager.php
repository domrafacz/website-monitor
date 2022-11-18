<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use App\Entity\Website;
use App\Repository\WebsiteRepository;

class WebsiteManager
{
    public function __construct(
        private readonly WebsiteRepository $websiteRepository,
    ) {}

    public function addOwner(Website $website, User $user, bool $flush): void
    {
        $user->addWebsite($website);
        $this->websiteRepository->save($website, $flush);
    }
}