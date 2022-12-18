<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\UserSettingsDto;
use App\Factory\UserSettingsFactory;
use App\Repository\UserRepository;
use App\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;

class UserSettingsManager
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly RequestStack $requestStack,
        private readonly UserSettingsFactory $userSettingsFactory,
    ) {
    }

    public function update(User $user, UserSettingsDto $userSettingsDto): void
    {
        $user->setLanguage($userSettingsDto->language);
        $this->updateLanguage($userSettingsDto->language);
        $this->userRepository->save($user, true);
    }

    public function get(User $user): UserSettingsDto
    {
        return $this->userSettingsFactory->createDto($user);
    }

    private function updateLanguage(string $language): void
    {
        $this->requestStack->getSession()->set('_locale', $language);
    }
}
