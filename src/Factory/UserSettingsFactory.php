<?php
declare(strict_types=1);

namespace App\Factory;

use App\Dto\UserSettingsDto;
use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

class UserSettingsFactory
{
    /** @var User $user */
    public function createDto(UserInterface $user): UserSettingsDto
    {
        $userSettings = new UserSettingsDto();
        $userSettings->language = $user->getLanguage();

        return $userSettings;
    }
}
