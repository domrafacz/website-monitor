<?php

declare(strict_types=1);

namespace App\Factory;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\NoPrivateNetworkHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HttpClientStaticFactory
{
    public static function create(bool $allowPrivateNetworks): HttpClientInterface
    {
        $client = HttpClient::create();

        return $allowPrivateNetworks ? $client : new NoPrivateNetworkHttpClient($client);
    }
}
