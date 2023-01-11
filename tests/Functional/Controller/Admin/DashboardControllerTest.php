<?php

namespace App\Tests\Functional\Controller\Admin;

use App\Tests\Functional\WebTestCase;

class DashboardControllerTest extends WebTestCase
{
    public function testDashboard(): void
    {
        $client = $this->getAuthenticatedClientByUsername('admin@test.com');

        $client->request('GET', '/admin/dashboard');

        $this->assertResponseIsSuccessful();
    }
}
