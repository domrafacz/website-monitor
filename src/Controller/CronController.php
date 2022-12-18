<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\WebsiteRepository;
use App\Service\RequestsRunner;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cron', name: 'cron_')]
class CronController extends AbstractController
{
    #[Route('/run-requests', name: 'run_requests')]
    public function runRequests(WebsiteRepository $websiteRepository, RequestsRunner $requestsRunner): Response
    {
        if ($websites = $websiteRepository->findAllReadyToUpdate()) {
            $requestsRunner->run($websites);
        }

        return new Response('OK');
    }
}
