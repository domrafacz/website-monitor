<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\RequestRunnerResponseDto;
use App\Dto\WebsiteDto;
use App\Entity\DowntimeLog;
use App\Entity\ResponseLog;
use App\Entity\User;
use App\Entity\Website;
use App\Repository\WebsiteRepository;
use App\Service\Notifier\Notifier;
use Symfony\Contracts\Translation\TranslatorInterface;

class WebsiteManager
{
    public function __construct(
        private readonly WebsiteRepository $websiteRepository,
        private readonly TranslatorInterface $translator,
        private readonly Notifier $notifier,
    ) {
    }

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

    /** @param array<int, string> $errors */
    public function addDowntimeLog(Website $website, \DateTimeImmutable $startTime, array $errors): void
    {
        $downtimeLog = new DowntimeLog();
        $downtimeLog->setWebsite($website);
        $downtimeLog->setStartTime($startTime);
        //TODO refactor initial error due to keeping translated errors in database
        $downtimeLog->setInitialError($errors);
        $website->addDowntimeLog($downtimeLog);
        $this->websiteRepository->save($website);
    }

    public function createDowntime(RequestRunnerResponseDto $dto, \DateTimeImmutable $startTime): bool
    {
        if (!$dto->website) {
            return false;
        }

        foreach ($dto->errors as $key => $error) {
            if (str_starts_with($error, 'request_runner')) {
                $dto->errors[$key] = $this->translator->trans(
                    $error,
                    [],
                    'messages',
                    $dto->website->getOwner()?->getLanguage()
                );
            }
        }

        $this->addDowntimeLog($dto->website, $startTime, $dto->errors);

        $message = sprintf(
            $this->translator->trans('request_runner_downtime', [], 'messages', $dto->website->getOwner()?->getLanguage()),
            $dto->website->getUrl(),
            implode("\n", $dto->errors)
        );

        $this->sendNotification(
            $dto->website,
            $this->translator->trans('request_runner_downtime_subject', [], 'messages', $dto->website->getOwner()?->getLanguage()),
            $message
        );

        return true;
    }

    public function endDowntime(RequestRunnerResponseDto $dto): bool
    {
        $downtimeLog = $dto->website?->getRecentDowntimeLog();

        if ($downtimeLog && $dto->website && $downtimeLog->getEndTime() == null) {
            $datetime = new \DateTimeImmutable();
            $datetime = $datetime->setTimestamp(intval($dto->startTime));

            $downtimeLog->setEndTime($datetime);

            $message = sprintf(
                $this->translator->trans('request_runner_downtime_end', [], 'messages', $dto->website->getOwner()?->getLanguage()),
                $dto->website->getUrl(),
                $downtimeLog->getStartTime()->format('Y-m-d H:i:s'),
                $datetime->format('Y-m-d H:i:s'),
            );

            // TODO add translation
            $this->sendNotification(
                $dto->website,
                $this->translator->trans('request_runner_downtime_end_subject', [], 'messages', $dto->website->getOwner()?->getLanguage()),
                $message
            );
        } else {
            return false;
        }

        return true;
    }

    /**
     * @param \DateTimeImmutable $startTime For example contains start time of cron job
     */
    public function addResponseLog(RequestRunnerResponseDto $dto, \DateTimeImmutable $startTime): void
    {
        //executes when site goes back up
        if (($dto->website?->getLastStatus() != Website::STATUS_OK) && $dto->status == Website::STATUS_OK) {
            $this->endDowntime($dto);
        }

        //executes when website goes down
        if (($dto->website?->getLastStatus() == Website::STATUS_OK) && $dto->status == Website::STATUS_ERROR) {
            $this->createDowntime($dto, $startTime);
        }

        $dto->website?->setLastCheck($startTime);
        $dto->website?->setLastStatus($dto->status);

        if ($dto->website && $dto->status === Website::STATUS_OK) {
            $dto->website = $this->updateCertExpireTime($dto->website, $dto->certExpireTime);

            $responseLog = new ResponseLog(
                $dto->website,
                $dto->status,
                $startTime,
                intval($dto->totalTime),
            );

            $dto->website->addResponseLog($responseLog);
        }
    }

    public function updateCertExpireTime(Website $website, ?\DateTimeInterface $certExpireTime = null): Website
    {
        if ($website->getCertExpiryTime() === null) {
            if ($certExpireTime !== null) {
                $website->setCertExpiryTime($certExpireTime);
            }
        } elseif ($certExpireTime !== null && $website->getCertExpiryTime() != $certExpireTime) {
            $message = sprintf(
                $this->translator->trans('request_runner_cert_changed', [], 'messages', $website->getOwner()?->getLanguage()),
                $website->getUrl(),
                $website->getCertExpiryTime()->format('Y-m-d H:i:s'),
                $certExpireTime->format('Y-m-d H:i:s'),
            );

            $this->sendNotification(
                $website,
                $this->translator->trans('request_runner_cert_changed_subject', [], 'messages', $website->getOwner()?->getLanguage()),
                $message
            );

            $website->setCertExpiryTime($certExpireTime);
        }

        return $website;
    }

    public function sendNotification(Website $website, string $subject, string $message): void
    {
        foreach ($website->getNotifierChannels() as $channel) {
            $this->notifier->sendNotification($channel->getType(), $subject, $message, $channel->getOptions());
        }
    }
}
