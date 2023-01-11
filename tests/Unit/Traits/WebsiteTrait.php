<?php

declare(strict_types=1);

namespace App\Tests\Unit\Traits;

use App\Entity\NotifierChannel;
use App\Entity\User;
use App\Entity\Website;

trait WebsiteTrait
{
    private function createWebsite(
        int $id,
        string $url = 'https://nonexistent.nonexistent',
        string $method = 'GET',
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

        $website->setUrl($url);
        $website->setRequestMethod($method);
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
}
