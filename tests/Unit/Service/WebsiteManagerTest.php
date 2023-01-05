<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Dto\RequestRunnerResponseDto;
use App\Repository\WebsiteRepository;
use App\Service\Notifier\Notifier;
use App\Service\WebsiteManager;
use App\Tests\Unit\Traits\WebsiteTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class WebsiteManagerTest extends TestCase
{
    use WebsiteTrait;

    public function testUpdateCertExpireTimeWhenNotSet(): void
    {
        $websiteManager = new WebsiteManager(
            $this->createMock(WebsiteRepository::class),
            $this->createMock(TranslatorInterface::class),
            $this->createMock(Notifier::class)
        );

        $website = $this->createWebsite(10);
        $newDate = new \DateTimeImmutable();

        $website = $websiteManager->updateCertExpireTime($website, $newDate);

        $this->assertEquals($newDate, $website->getCertExpiryTime());
    }

    public function testUpdateCertExpireTime(): void
    {
        $websiteManager = new WebsiteManager(
            $this->createMock(WebsiteRepository::class),
            $this->createMock(TranslatorInterface::class),
            $this->createMock(Notifier::class)
        );

        $website = $this->createWebsite(10);
        $newDate = new \DateTimeImmutable();
        $oldDate = $newDate->setTimestamp($newDate->getTimestamp() - 1000);
        $website->setCertExpiryTime($oldDate);

        $this->assertEquals($oldDate, $website->getCertExpiryTime());

        $website = $websiteManager->updateCertExpireTime($website, $newDate);

        $this->assertEquals($newDate, $website->getCertExpiryTime());
    }

    public function testCreateDowntimeInvalidDto(): void
    {
        $websiteManager = new WebsiteManager(
            $this->createMock(WebsiteRepository::class),
            $this->createMock(TranslatorInterface::class),
            $this->createMock(Notifier::class)
        );

        $dto = new RequestRunnerResponseDto();

        $this->assertFalse($websiteManager->createDowntime($dto, new \DateTimeImmutable()));
    }
}
