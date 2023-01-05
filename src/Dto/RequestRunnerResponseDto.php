<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Website;

class RequestRunnerResponseDto
{
    public ?Website $website = null;

    /** @var array<int, string> $errors  */
    public array $errors = [];

    public ?float $startTime = null;
    public ?float $totalTime = null;

    public ?int $statusCode = null;

    public ?\DateTimeInterface $certExpireTime = null;

    public int $status = Website::STATUS_OK;
}
