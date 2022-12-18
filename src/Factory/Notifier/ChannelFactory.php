<?php

declare(strict_types=1);

namespace App\Factory\Notifier;

use App\Dto\NotifierChannelDto;

class ChannelFactory
{
    /** @param array<string, string> $formData */
    public function createDtoFromFormData(array $formData): NotifierChannelDto
    {
        $dto = new NotifierChannelDto();

        if (isset($formData['name'])) {
            $dto->name = $formData['name'];
            unset($formData['name']);
        }

        $dto->options = $formData;

        return $dto;
    }
}
