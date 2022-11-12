<?php
declare(strict_types=1);

namespace App\Tests\Unit\Validator;

use App\Validator\PasswordStrength;
use App\Validator\PasswordStrengthValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * @extends ConstraintValidatorTestCase<PasswordStrengthValidator>
 */
class PasswordStrengthValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): PasswordStrengthValidator
    {
        return new PasswordStrengthValidator();
    }

    public function testNullIsValid(): void
    {
        $this->validator->validate(null, new PasswordStrength());

        $this->assertNoViolation();
    }

    public function testEmptyStringIsValid(): void
    {
        $this->validator->validate('', new PasswordStrength());

        $this->assertNoViolation();
    }

    public function testExpectsStringCompatibleType(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->validator->validate(new \stdClass(), new PasswordStrength());
    }

    public function testExpectsCompatibleConstrain(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->validator->validate('Test123$', new NotBlank());
    }

    public function testPasswordIsValid(): void
    {
        $this->validator->validate('Test123$', new PasswordStrength());

        $this->assertNoViolation();
    }
}
