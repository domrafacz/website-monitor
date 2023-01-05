<?php

namespace App\Tests\Integration\Service;

use App\Dto\RequestRunnerResponseDto;
use App\Entity\User;
use App\Entity\Website;
use App\Repository\UserRepository;
use App\Service\WebsiteManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class WebsiteManagerTest extends KernelTestCase
{
    private ?WebsiteManager $websiteManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->websiteManager = $this->getContainer()->get(WebsiteManager::class);
    }

    public function testEndDowntimeWithInvalidWebsite(): void
    {
        $response = new RequestRunnerResponseDto();
        $this->assertFalse($this->websiteManager->endDowntime($response));
    }

    public function testAddResponseLogWhenWebsiteGoesBackUp(): void
    {
        $downtimeEnd = new \DateTimeImmutable();
        /** @var User $user */
        $user = $this->getContainer()->get(UserRepository::class)->findOneByUsername('test1@test.com');
        $website = $user->getWebsites()->first();
        $website->setLastStatus(Website::STATUS_ERROR);
        $response = new RequestRunnerResponseDto();
        $response->website = $website;
        $response->statusCode = $website->getExpectedStatusCode();
        $response->status = Website::STATUS_OK;
        $response->certExpireTime = $website->getCertExpiryTime();
        $response->totalTime = 250;

        $this->assertEquals(Website::STATUS_ERROR, $website->getLastStatus());
        $this->websiteManager->addResponseLog($response, $downtimeEnd);
        $this->assertEquals(Website::STATUS_OK, $website->getLastStatus());
    }
}
