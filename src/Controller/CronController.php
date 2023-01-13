<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ResponseLog;
use App\Repository\WebsiteRepository;
use App\Service\RequestsRunner;
use App\Service\ResponseLogArchiver;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cron', name: 'cron_')]
class CronController extends AbstractController
{
    #[Route('/run-requests', name: 'run_requests')]
    public function runRequests(WebsiteRepository $websiteRepository, RequestsRunner $requestsRunner, ResponseLogArchiver $archiver, LoggerInterface $logger, #[Autowire('%app.archiveLimit%')]int $archiveLimit): Response
    {
        if ($websites = $websiteRepository->findAllReadyToUpdate()) {
            $requestsRunner->run($websites, true);
        }

        $statistics = $requestsRunner->getStatistics();

        if (isset($statistics['successful'], $statistics['failed'])) {
            $logger->info(sprintf('Cron request runner results, successful: %d, failed: %d', $statistics['successful'], $statistics['failed']));
        }

        $currentTime = new \DateTimeImmutable();
        $dueTime = $currentTime->setTimestamp($currentTime->getTimestamp() - (ResponseLog::RETENTION_PERIOD_IN_DAYS * 86400));
        $dueTime = new \DateTimeImmutable($dueTime->format('Y-m-d 23:59:59'));

        $archivedLogs = 0;

        $websitesToArchive = [];

        //sets next archive time before archiving to prevent accidental double archiving by another cron run
        if ($websites) {
            foreach ($websites as $website) {
                if ($archivedLogs >= $archiveLimit) {
                    break;
                }

                if ($website->canArchiveResponseLog()) {
                    $website->setNextArchiveTime($currentTime->modify('+4 hour'));
                    $websitesToArchive[] = $website;
                    $archivedLogs++;
                }
            }
        }

        foreach ($websitesToArchive as $website) {
            $archiver->archive($website, $dueTime);
        }

        $websiteRepository->saveMultiple($websitesToArchive, true);

        return new Response('OK');
    }
}
