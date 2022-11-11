<?php
declare(strict_types=1);

namespace App\Service;

use App\Dto\UserSettingsDto;
use App\Factory\UserSettingsFactory;
use App\Repository\UserRepository;
use App\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;

class UserSettingsManager
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly RequestStack $requestStack,
        private readonly UserSettingsFactory $userSettingsFactory,
    ) {}

    public function update(UserInterface $user, UserSettingsDto $userSettingsDto): void
    {
        /** @var User $user */
        $user->setLanguage($userSettingsDto->language);
        $this->updateLanguage($userSettingsDto->language);
        $this->userRepository->save($user, true);
    }

    public function get(UserInterface $user): UserSettingsDto
    {
        return $this->userSettingsFactory->createDto($user);
    }

    private function updateLanguage(string $language): void
    {
        $this->requestStack->getSession()->set('_locale', $language);
    }
}
