<?php
declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\DowntimeLog;
use App\Entity\NotifierChannel;
use App\Entity\ResponseLog;
use App\Entity\User;
use App\Entity\Website;
use PHPUnit\Framework\TestCase;

class WebsiteTest extends TestCase
{
    public function testSetterAndGetter(): void
    {
        $user = new class extends User {

            public function getId(): int {
                return 1;
            }
        };

        $website = new class extends Website {

            public function getId(): int {
                return 1;
            }
        };

        $responseLog = new class(
            $website,
            ResponseLog::STATUS_OK,
            new \DateTimeImmutable(),
            500,

        ) extends ResponseLog {

            public function getId(): int {
                return 1;
            }
        };

        $downtimeLog = new class extends DowntimeLog {

            public function getId(): int {
                return 1;
            }
        };

        $notifierChannel = new class($user, 0, 'test') extends NotifierChannel {

            public function getId(): int {
                return 1;
            }
        };

        $lastCheck = new \DateTimeImmutable();
        $certExpireTime = $lastCheck->setTimestamp($lastCheck->getTimestamp() + 86400);

        $this->assertNull((new Website())->getId());
        $this->assertSame(1, $website->getId());

        $this->assertInstanceOf(Website::class, $website->setOwner($user));
        $this->assertSame($user, $website->getOwner());

        $this->assertInstanceOf(Website::class, $website->setUrl('https://test.com'));
        $this->assertSame('https://test.com', $website->getUrl());

        $this->assertInstanceOf(Website::class, $website->setRequestMethod('POST'));
        $this->assertSame('POST', $website->getRequestMethod());

        $this->assertInstanceOf(Website::class, $website->setMaxRedirects(5));
        $this->assertSame(5, $website->getMaxRedirects());

        $this->assertInstanceOf(Website::class, $website->setTimeout(5));
        $this->assertSame(5, $website->getTimeout());

        $this->assertInstanceOf(Website::class, $website->setLastStatus(Website::STATUS_ERROR));
        $this->assertSame(Website::STATUS_ERROR, $website->getLastStatus());

        $this->assertInstanceOf(Website::class, $website->setLastCheck($lastCheck));
        $this->assertSame($lastCheck, $website->getLastCheck());

        $this->assertInstanceOf(Website::class, $website->setCertExpiryTime($certExpireTime));
        $this->assertSame($certExpireTime, $website->getCertExpiryTime());

        $this->assertInstanceOf(Website::class, $website->setFrequency(1));
        $this->assertSame(1, $website->getFrequency());

        $this->assertInstanceOf(Website::class, $website->setEnabled(true));
        $this->assertSame(true, $website->isEnabled());

        $this->assertInstanceOf(Website::class, $website->addResponseLog($responseLog));
        $this->assertSame(1, $website->getResponseLogs()->count());

        $this->assertInstanceOf(Website::class, $website->removeResponseLog($responseLog));
        $this->assertSame(0, $website->getResponseLogs()->count());

        $this->assertInstanceOf(Website::class, $website->setExpectedStatusCode(502));
        $this->assertSame(502, $website->getExpectedStatusCode());

        $this->assertInstanceOf(Website::class, $website->addDowntimeLog($downtimeLog));
        $this->assertSame(1, $website->getDowntimeLogs()->count());
        $this->assertInstanceOf(DowntimeLog::class, $website->getRecentDowntimeLog());

        $this->assertInstanceOf(Website::class, $website->removeDowntimeLog($downtimeLog));
        $this->assertSame(0, $website->getDowntimeLogs()->count());
        $this->assertNull($website->getRecentDowntimeLog());

        $this->assertInstanceOf(Website::class, $website->addNotifierChannel($notifierChannel));
        $this->assertSame(1, $website->getNotifierChannels()->count());

        $this->assertSame(true, $website->hasNotifierChannel(1));
        $this->assertSame(false, $website->hasNotifierChannel(2));

        $website->toggleNotifierChannel($notifierChannel);
        $this->assertSame(0, $website->getNotifierChannels()->count());

        $website->toggleNotifierChannel($notifierChannel);
        $this->assertSame(1, $website->getNotifierChannels()->count());

        $this->assertInstanceOf(Website::class, $website->removeNotifierChannel($notifierChannel));
        $this->assertSame(0, $website->getNotifierChannels()->count());
    }
}