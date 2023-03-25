<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class PasswordStrengthValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof PasswordStrength) {
            throw new UnexpectedTypeException($constraint, PasswordStrength::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        $violation = false;

        if (strlen($value) < $constraint->minLength || strlen($value) > $constraint->maxLength) {
            $violation = true;
        }

        $pattern = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^\w\s])(?=.*\p{L}).{'.$constraint->minLength.',}$/u';

        if (!preg_match($pattern, $value)) {
            $violation = true;
        }

        if ($violation === true) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
