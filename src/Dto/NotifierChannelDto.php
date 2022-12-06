<?php
declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class NotifierChannelDto
{
    #[Assert\Length(
        max: 255,
    )]
    public string $name;

    public array $options;
}