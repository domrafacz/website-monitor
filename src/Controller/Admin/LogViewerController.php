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
    ) {}

    #[Route('/logs', name: 'logs')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(Request $request): Response
    {
        $logFiles = $this->getLogFiles($this->logDir);
        
        $selectedFile = $request->query->get('file', 'dev.log');
        $lines = (int) $request->query->get('lines', 100);
        $search = $request->query->get('search', '');
        $level = $request->query->get('level', '');
        
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
        
        $selectedFile = $request->query->get('file', 'dev.log');
        
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
    
    private function getLogFiles(string $logDir): array
    {
        if (!is_dir($logDir)) {
            return [];
        }
        
        $files = scandir($logDir);
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
    
    private function readLogFile(string $logPath, int $lines, string $search = '', string $level = ''): array
    {
        if (!file_exists($logPath)) {
            return [];
        }
        
        $content = [];
        $file = new \SplFileObject($logPath);
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();
        
        $startLine = max(0, $totalLines - $lines);
        $file->seek($startLine);
        
        $logEntry = [];
        $lineNumber = $startLine;
        
        while (!$file->eof()) {
            $line = $file->current();
            $file->next();
            $lineNumber++;
            
            // Match Symfony log format: [YYYY-MM-DD HH:MM:SS] channel.level: message
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2}[T\s]\d{2}:\d{2}:\d{2}[^\]]*)\]\s+(\w+)\.(\w+):\s+(.*)/', $line, $matches)) {
                // Save previous entry if exists
                if (!empty($logEntry)) {
                    $this->addLogEntry($content, $logEntry, $search, $level);
                }
                
                // Start new entry
                $logEntry = [
                    'datetime' => $matches[1],
                    'level' => strtoupper($matches[3]),
                    'channel' => $matches[2],
                    'message' => $matches[4],
                    'line_number' => $lineNumber,
                    'raw' => $line,
                ];
            } elseif (!empty($logEntry)) {
                // Continuation of previous log entry
                $logEntry['message'] .= "\n" . $line;
                $logEntry['raw'] .= $line;
            } else {
                // Unformatted line
                $content[] = [
                    'datetime' => '',
                    'level' => 'INFO',
                    'channel' => '',
                    'message' => $line,
                    'line_number' => $lineNumber,
                    'raw' => $line,
                ];
            }
        }
        
        // Add last entry
        if (!empty($logEntry)) {
            $this->addLogEntry($content, $logEntry, $search, $level);
        }
        
        return array_reverse($content); // Show newest first
    }
    
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
