<?php

namespace App\Tests\Functional\Controller\Admin;

use App\Tests\Functional\WebTestCase;

class LogViewerControllerTest extends WebTestCase
{
    public function testLogsPageIsAccessibleForAdminUser(): void
    {
        $client = $this->getAuthenticatedClientByUsername('admin@test.com');

        $client->request('GET', '/admin/logs');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Log viewer', $client->getResponse()->getContent());
    }

    public function testLogsPageRequiresAdminRole(): void
    {
        $client = $this->createClient();

        $client->request('GET', '/admin/logs');

        $this->assertResponseRedirects('/login');
    }

    public function testLogsPageWithFileParameter(): void
    {
        $client = $this->getAuthenticatedClientByUsername('admin@test.com');

        $client->request('GET', '/admin/logs', ['file' => 'dev.log']);

        $this->assertResponseIsSuccessful();
    }

    public function testLogsPageWithLinesParameter(): void
    {
        $client = $this->getAuthenticatedClientByUsername('admin@test.com');

        $client->request('GET', '/admin/logs', ['lines' => '100']);

        $this->assertResponseIsSuccessful();
    }

    public function testLogsPageWithLevelFilter(): void
    {
        $client = $this->getAuthenticatedClientByUsername('admin@test.com');

        $client->request('GET', '/admin/logs', ['level' => 'ERROR']);

        $this->assertResponseIsSuccessful();
    }

    public function testLogsPageWithSearchFilter(): void
    {
        $client = $this->getAuthenticatedClientByUsername('admin@test.com');

        $client->request('GET', '/admin/logs', ['search' => 'error']);

        $this->assertResponseIsSuccessful();
    }

    public function testLogsPageWithMultipleFilters(): void
    {
        $client = $this->getAuthenticatedClientByUsername('admin@test.com');

        $client->request('GET', '/admin/logs', [
            'file' => 'dev.log',
            'lines' => '250',
            'level' => 'WARNING',
            'search' => 'test',
        ]);

        $this->assertResponseIsSuccessful();
    }

    public function testLogsDownloadIsAccessibleForAdminUser(): void
    {
        $client = $this->getAuthenticatedClientByUsername('admin@test.com');

        $client->request('GET', '/admin/logs/download', ['file' => 'dev.log']);

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('attachment', $client->getResponse()->headers->get('Content-Disposition'));
    }

    public function testLogsDownloadRequiresAdminRole(): void
    {
        $client = $this->createClient();

        $client->request('GET', '/admin/logs/download', ['file' => 'dev.log']);

        $this->assertResponseRedirects('/login');
    }

    public function testLogsDownloadWithInvalidFile(): void
    {
        $client = $this->getAuthenticatedClientByUsername('admin@test.com');

        $client->request('GET', '/admin/logs/download', ['file' => 'nonexistent.log']);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testLogsDownloadContentDisposition(): void
    {
        $client = $this->getAuthenticatedClientByUsername('admin@test.com');

        $client->request('GET', '/admin/logs/download', ['file' => 'dev.log']);

        $this->assertResponseIsSuccessful();
        $headers = $client->getResponse()->headers;
        $this->assertTrue($headers->has('Content-Disposition'));
        $this->assertStringContainsString('dev.log', $headers->get('Content-Disposition'));
    }
}
