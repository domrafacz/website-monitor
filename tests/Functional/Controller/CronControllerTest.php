<?php
declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CronControllerTest extends WebTestCase
{
    public function testRunRequests(): void
    {
        $client = static::createClient();
        $client->request('GET', '/cron/run-requests');
        $this->assertResponseIsSuccessful();
    }

}