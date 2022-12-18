<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;

class UserSettingsDeleteUserDto
{
    #[SecurityAssert\UserPassword(
        message: 'incorrect_password',
    )]
    public string $plainPassword;
}
