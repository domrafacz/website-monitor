<?php

namespace App\Validator;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[\Attribute]
class PasswordStrength extends Constraint
{
    public string $message = 'The password must be at least 8 characters long and must contain at least one digit, one letter and one nonalphanumeric character.';

    public int $minLength = 8;
    public int $maxLength = 4096;

    #[HasNamedArguments]
    public function __construct(int $minLength = 8, int $maxLength = 4096, array $groups = null, mixed $payload = null)
    {
        parent::__construct([], $groups, $payload);

        $this->minLength = $minLength;
        $this->maxLength = $maxLength;
    }
}
