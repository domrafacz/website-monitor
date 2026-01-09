<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Website;
use App\Repository\ResponseLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ChartDataController extends AbstractController
{
    private const PERIOD_CONFIG = [
        '1h' => ['seconds' => 3600, 'interval' => 60],           // 1 hour: 1 min intervals, ~60 points
        '24h' => ['seconds' => 86400, 'interval' => 300],        // 24h: 5 min intervals, ~288 points
        '7d' => ['seconds' => 604800, 'interval' => 1800],       // 7 days: 30 min intervals, ~336 points
        '30d' => ['seconds' => 2592000, 'interval' => 7200],     // 30 days: 2 hour intervals, ~360 points
        '3m' => ['seconds' => 7776000, 'interval' => 21600],     // 3 months: 6 hour intervals, ~360 points
        '6m' => ['seconds' => 15552000, 'interval' => 43200],    // 6 months: 12 hour intervals, ~360 points
        '1y' => ['seconds' => 31536000, 'interval' => 86400],    // 1 year: daily intervals, ~365 points
    ];

    public function __construct(
        private readonly ResponseLogRepository $responseLogRepository,
    ) {
    }

    #[Route('/api/website/{id}/chart-data', name: 'api_website_chart_data', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[IsGranted('view', subject: 'website', statusCode: 404)]
    public function getChartData(Website $website, Request $request): JsonResponse
    {
        $period = $request->query->get('period', '24h');

        if (!isset(self::PERIOD_CONFIG[$period])) {
            return new JsonResponse(['error' => 'Invalid period'], 400);
        }

        $config = self::PERIOD_CONFIG[$period];
        $endTime = new \DateTimeImmutable();
        $startTime = $endTime->modify("-{$config['seconds']} seconds");

        $data = $this->responseLogRepository->getAggregatedChartData(
            $website,
            $startTime,
            $endTime,
            $config['interval']
        );

        // Transform data for ApexCharts format
        $uptimeData = [];
        $latencyData = [];

        foreach ($data as $point) {
            $timestamp = (int) $point['bucket_time']->format('U') * 1000; // ApexCharts expects milliseconds
            $uptimeData[] = [$timestamp, round((float) $point['uptime_percent'], 2)];
            $latencyData[] = [$timestamp, (int) $point['avg_response_time']];
        }

        return new JsonResponse([
            'uptime' => $uptimeData,
            'latency' => $latencyData,
            'period' => $period,
        ]);
    }
}
