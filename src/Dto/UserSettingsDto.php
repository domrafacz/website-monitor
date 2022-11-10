<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class UserSettingsDto
{
    #[Assert\NotBlank]
    public ?string $language = null;
}