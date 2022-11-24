<?php
declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class WebsiteDto
{
    /*
    TODO add custom method type
    TODO add custom headers
    */

    #[Assert\Url]
    #[Assert\Length(
        max: 255,
    )]
    public string $url;

    #[Assert\Length(
        max: 100,
    )]
    public string $requestMethod = 'GET';

    #[Assert\Range(
        notInRangeMessage: 'validator_range',
        min: 0,
        max: 20,
    )]
    public int $maxRedirects = 0;

    #[Assert\Range(
        notInRangeMessage: 'validator_range',
        min: 5,
        max: 30,
    )]
    public int $timeout = 30;

    #[Assert\Positive]
    public int $frequency = 1;

    public bool $enabled = true;

    #[Assert\Range(
        notInRangeMessage: 'validator_range',
        min: 100,
        max: 599,
    )]
    public int $expectedStatusCode = 200;

}