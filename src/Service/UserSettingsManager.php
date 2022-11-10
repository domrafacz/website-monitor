<?php

namespace App\Service;

use App\Dto\UserSettingsDto;
use App\Repository\UserRepository;
use App\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;

class UserSettingsManager
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly RequestStack $requestStack,
    ) {}

    /** @var User $user */
    public function update(UserInterface $user, UserSettingsDto $userSettingsDto): void
    {
        $user->setLanguage($userSettingsDto->language);
        $this->updateLanguage($userSettingsDto->language);
        $this->userRepository->save($user, true);
    }

    private function updateLanguage(string $language): void
    {
        $this->requestStack->getSession()->set('_locale', $language);
    }
}