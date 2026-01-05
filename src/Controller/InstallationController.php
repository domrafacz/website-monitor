<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\DBAL\DriverManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Attribute\Route;

class InstallationController extends AbstractController
{
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        #[Autowire(param: 'kernel.project_dir')] private readonly string $projectDir
    )
    {
    }

    #[Route('/installation')]
    public function redirectLocale(Request $request): Response
    {
        return $this->redirectToRoute('app_installation', ['_locale' => $request->getLocale()]);
    }

    #[Route('/{_locale}/installation', name: 'app_installation', requirements: ['_locale' => '%app.locales%'])]
    public function installation(Request $request): Response
    {
        $envPath = $this->projectDir . '.env';
        if (!$this->filesystem->exists($envPath)) {
            return $this->render('installation.html.twig', [
                'alreadyInstalled' => true,
            ]);
        }

        $errors = [];
        $createdPassword = null;

        if ($request->isMethod('POST')) {
            $dbHost = (string) $request->request->get('db_host', '127.0.0.1');
            $dbPort = (string) $request->request->get('db_port', '5432');
            $dbName = (string) $request->request->get('db_name', 'app');
            $dbUser = (string) $request->request->get('db_user', 'app');
            $dbPassword = (string) $request->request->get('db_password', '!ChangeMe!');
            $dbDriver = (string) $request->request->get('db_driver', 'postgresql');
            $serverVersion = (string) $request->request->get('server_version', '17');
            $adminEmail = (string) $request->request->get('admin_email', 'admin@example.com');

            if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid admin email';
            }

            if (empty($errors)) {
                // Prepare DATABASE_URL
                $userPart = rawurlencode($dbUser);
                $passPart = rawurlencode($dbPassword);
                $dsn = sprintf('%s://%s:%s@%s:%s/%s?serverVersion=%s&charset=utf8', $dbDriver, $userPart, $passPart, $dbHost, $dbPort, $dbName, $serverVersion);

                // Copy .env.example to .env
                $envExample = $this->projectDir . '/.env.example';
                try {
                    if (!$this->filesystem->exists($envExample)) {
                        $errors[] = '.env.example not found in project root';
                    } else {
                        $this->filesystem->copy($envExample, $envPath);

                        // Parse and update .env using proper handling
                        $envContents = file_get_contents($envPath);
                        if ($envContents === false) {
                            throw new \RuntimeException('Failed to read .env after copy');
                        }

                        // Parse and update .env using more robust approach
                        $envData = $this->parseEnvFile($envContents);
                        $envData['DATABASE_URL'] = $dsn;
                        $newEnvContent = $this->buildEnvContent($envContents, $envData);
                        file_put_contents($envPath, $newEnvContent);
                    }
                } catch (\Throwable $e) {
                    $errors[] = 'Failed to create .env: ' . $e->getMessage();
                }

                // Check DB connection
                if (empty($errors)) {
                    try {
                        $connection = DriverManager::getConnection(['url' => $dsn]);
                        $connection->connect();
                    } catch (\Throwable $e) {
                        $errors[] = 'Database connection failed: ' . $e->getMessage();
                    }
                }

                // Run migrations to populate schema
                if (empty($errors)) {
                    try {
                        $console = $this->projectDir . '/bin/console';
                        $process = new Process([
                            PHP_BINARY,
                            $console,
                            'doctrine:migrations:migrate',
                            '--no-interaction',
                            '--allow-no-migration',
                        ]);
                        $process->setWorkingDirectory($this->projectDir);
                        $process->run();

                        if (!$process->isSuccessful()) {
                            throw new \RuntimeException('Migrations failed: ' . $process->getErrorOutput() . $process->getOutput());
                        }
                    } catch (\Throwable $e) {
                        $errors[] = 'Failed to run migrations: ' . $e->getMessage();
                    }
                }

                // Create admin user
                if (empty($errors)) {
                    try {
                        $user = new User();
                        $user->setEmail($adminEmail);
                        $user->setRoles(array_merge($user->getRoles(), ['ROLE_ADMIN']));

                        $plainPassword = bin2hex(random_bytes(16));

                        $hashed = $this->userPasswordHasher->hashPassword($user, $plainPassword);
                        $user->setPassword($hashed);

                        $this->userRepository->save($user, true);

                        $createdPassword = $plainPassword;
                    } catch (\Throwable $e) {
                        $errors[] = 'Failed to create admin user: ' . $e->getMessage();
                    }
                }
            }
        }

        return $this->render('installation.html.twig', [
            'errors' => $errors,
            'createdPassword' => $createdPassword,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function parseEnvFile(string $content): array
    {
        $envData = [];
        $lines = explode("\n", $content);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }
            
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                if (($value[0] ?? '') === '"' && ($value[-1] ?? '') === '"') {
                    $value = substr($value, 1, -1);
                } elseif (($value[0] ?? '') === "'" && ($value[-1] ?? '') === "'") {
                    $value = substr($value, 1, -1);
                }
                
                $envData[$key] = $value;
            }
        }
        
        return $envData;
    }

    /**
     * @param array<string, string> $envData
     */
    private function buildEnvContent(string $originalContent, array $envData): string
    {
        $lines = explode("\n", $originalContent);
        $newLines = [];
        $processedKeys = [];
        
        foreach ($lines as $line) {
            $trimmedLine = trim($line);

            if (empty($trimmedLine) || str_starts_with($trimmedLine, '#')) {
                $newLines[] = $line;
                continue;
            }

            if (str_contains($trimmedLine, '=')) {
                [$key] = explode('=', $trimmedLine, 2);
                $key = trim($key);
                
                if (isset($envData[$key])) {
                    $value = $envData[$key];
                    $newLines[] = $key . '="' . $value . '"';
                    $processedKeys[] = $key;
                } else {
                    $newLines[] = $line;
                }
            } else {
                $newLines[] = $line;
            }
        }

        foreach ($envData as $key => $value) {
            if (!in_array($key, $processedKeys, true)) {
                $newLines[] = $key . '="' . $value . '"';
            }
        }
        
        return implode("\n", $newLines);
    }
}

