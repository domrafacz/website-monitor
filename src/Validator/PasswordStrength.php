<?php
declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[\Attribute]
class PasswordStrength extends Constraint
{
    public string $message = 'password_strength_message';

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
