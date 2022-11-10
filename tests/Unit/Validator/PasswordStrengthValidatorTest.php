<?php
declare(strict_types=1);

namespace App\Tests\Unit\Validator;

use App\Validator\PasswordStrength;
use App\Validator\PasswordStrengthValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @extends ConstraintValidatorTestCase<PasswordStrengthValidator>
 */
class PasswordStrengthValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): PasswordStrengthValidator
    {
        return new PasswordStrengthValidator();
    }

    public function testPasswordIsValid(): void
    {
        $this->validator->validate("Test123$", new PasswordStrength());

        $this->assertNoViolation();
    }
}
