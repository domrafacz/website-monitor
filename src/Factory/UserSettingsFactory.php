<?php
declare(strict_types=1);

namespace App\Factory;

use App\Dto\UserSettingsDto;
use App\Entity\User;

class UserSettingsFactory
{
    public function createDto(User $user): UserSettingsDto
    {
        $userSettings = new UserSettingsDto();
        $userSettings->language = $user->getLanguage();

        return $userSettings;
    }
}
