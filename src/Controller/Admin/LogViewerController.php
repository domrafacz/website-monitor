<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin', name: 'app_admin_')]
class LogViewerController extends AbstractController
{
    public function __construct(
        #[Autowire('%kernel.logs_dir%')]
        private readonly string $logDir,
    ) {
    }

    #[Route('/logs', name: 'logs')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(Request $request): Response
    {
        $logFiles = $this->getLogFiles($this->logDir);

        $selectedFile = $request->query->getString('file', 'dev.log');
        $lines = $request->query->getInt('lines', 100);
        $search = $request->query->getString('search', '');
        $level = $request->query->getString('level', '');

        // Validate selected file is in log directory
        if (!in_array($selectedFile, $logFiles)) {
            $selectedFile = $logFiles[0] ?? 'dev.log';
        }

        $logPath = $this->logDir . '/' . $selectedFile;
        $logContent = $this->readLogFile($logPath, $lines, $search, $level);

        return $this->render('admin/logs.html.twig', [
            'log_files' => $logFiles,
            'selected_file' => $selectedFile,
            'log_content' => $logContent,
            'lines' => $lines,
            'search' => $search,
            'level' => $level,
        ]);
    }

    #[Route('/logs/download', name: 'logs_download')]
    #[IsGranted('ROLE_ADMIN')]
    public function download(Request $request): Response
    {
        $logFiles = $this->getLogFiles($this->logDir);

        $selectedFile = $request->query->getString('file', 'dev.log');

        // Validate selected file is in log directory
        if (!in_array($selectedFile, $logFiles)) {
            throw $this->createAccessDeniedException('Invalid log file.');
        }

        $logPath = $this->logDir . '/' . $selectedFile;

        if (!file_exists($logPath)) {
            throw $this->createAccessDeniedException('Log file not found.');
        }

        $response = new BinaryFileResponse($logPath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $selectedFile
        );

        return $response;
    }

    /**
     * @return array<int, string>
     */
    private function getLogFiles(string $logDir): array
    {
        if (!is_dir($logDir)) {
            return [];
        }

        $files = scandir($logDir);
        if ($files === false) {
            return [];
        }

        $logFiles = [];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $logDir . '/' . $file;
            if (is_file($filePath) && pathinfo($file, PATHINFO_EXTENSION) === 'log') {
                $logFiles[] = $file;
            }
        }

        rsort($logFiles); // Sort newest first

        return $logFiles;
    }

    /**
     * @return array<int, array{datetime: string, level: string, channel: string, message: string, line_number: int, raw: string}>
     */
    private function readLogFile(string $logPath, int $lines, string $search = '', string $level = ''): array
    {
        if (!file_exists($logPath)) {
            return [];
        }

        /** @var array<int, array{datetime: string, level: string, channel: string, message: string, line_number: int, raw: string}> $content */
        $content = [];
        $file = new \SplFileObject($logPath);
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();

        $startLine = max(0, $totalLines - $lines);
        $file->seek($startLine);

        /** @var array{datetime: string, level: string, channel: string, message: string, line_number: int, raw: string}|null $logEntry */
        $logEntry = null;
        $lineNumber = $startLine;

        while (!$file->eof()) {
            $line = $file->current();
            $lineContent = is_string($line) ? $line : '';
            $file->next();
            $lineNumber++;

            // Match Symfony log format: [YYYY-MM-DD HH:MM:SS] channel.level: message
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2}[T\s]\d{2}:\d{2}:\d{2}[^\]]*)\]\s+(\w+)\.(\w+):\s+(.*)/', $lineContent, $matches)) {
                // Save previous entry if exists
                if ($logEntry !== null) {
                    $this->addLogEntry($content, $logEntry, $search, $level);
                }

                // Start new entry
                $logEntry = [
                    'datetime' => $matches[1],
                    'level' => strtoupper($matches[3]),
                    'channel' => $matches[2],
                    'message' => $matches[4],
                    'line_number' => $lineNumber,
                    'raw' => $lineContent,
                ];
            } elseif ($logEntry !== null) {
                // Continuation of previous log entry
                $logEntry['message'] .= "\n" . $lineContent;
                $logEntry['raw'] .= $lineContent;
            } else {
                // Unformatted line
                $content[] = [
                    'datetime' => '',
                    'level' => 'INFO',
                    'channel' => '',
                    'message' => $lineContent,
                    'line_number' => $lineNumber,
                    'raw' => $lineContent,
                ];
            }
        }

        // Add last entry
        if ($logEntry !== null) {
            $this->addLogEntry($content, $logEntry, $search, $level);
        }

        return array_reverse($content); // Show newest first
    }

    /**
     * @param array<int, array{datetime: string, level: string, channel: string, message: string, line_number: int, raw: string}> $content
     * @param array{datetime: string, level: string, channel: string, message: string, line_number: int, raw: string} $logEntry
     */
    private function addLogEntry(array &$content, array $logEntry, string $search, string $level): void
    {
        // Filter by search term
        if ($search !== '' && stripos($logEntry['message'], $search) === false) {
            return;
        }

        // Filter by level
        if ($level !== '' && strcasecmp($logEntry['level'], $level) !== 0) {
            return;
        }

        $content[] = $logEntry;
    }
}
