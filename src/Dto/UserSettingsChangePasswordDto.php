<?php

declare(strict_types=1);

namespace App\Dto;

use App\Validator\PasswordStrength;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;

class UserSettingsChangePasswordDto
{
    #[SecurityAssert\UserPassword(
        message: 'incorrect_password',
    )]
    public string $currentPassword;

    #[PasswordStrength]
    public string $newPassword;
}
