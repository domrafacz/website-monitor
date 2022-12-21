<?php

declare(strict_types=1);

namespace App\Tests\Unit\Factory;

use App\Factory\HttpClientStaticFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\NoPrivateNetworkHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HttpClientStaticFactoryTest extends TestCase
{
    public function testCreateHttpClient(): void
    {
        $this->assertInstanceOf(HttpClientInterface::class, HttpClientStaticFactory::create(true));
        $this->assertInstanceOf(NoPrivateNetworkHttpClient::class, HttpClientStaticFactory::create(false));
    }
}
