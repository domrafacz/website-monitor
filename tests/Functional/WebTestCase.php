<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

abstract class WebTestCase extends BaseWebTestCase
{
    protected function getAuthenticatedClientByUsername(string $username): KernelBrowser
    {
        $client = $this->createClient();

        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneByUsername($username);

        if (!$user) {
            throw new UserNotFoundException(sprintf('User not found: %s', $username));
        }

        return $client->loginUser($user);
    }
}