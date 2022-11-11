<?php
declare(strict_types=1);

namespace App\Factory;

use App\Dto\UserSettingsDto;
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

class UserSettingsFactory
{
    public function createDto(UserInterface $user): UserSettingsDto
    {
        /** @var User $user */
        $userSettings = new UserSettingsDto();
        $userSettings->language = $user->getLanguage();

        return $userSettings;
    }
}
