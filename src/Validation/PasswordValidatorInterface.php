<?php

declare(strict_types=1);

namespace Terminal42\PasswordValidationBundle\Validation;

use Terminal42\PasswordValidationBundle\Exception\PasswordValidatorException;

interface PasswordValidatorInterface
{
    /**
     * @param ValidationContext $context holds the password to verify, the user id and the like
     *
     * @return bool true to accept the password, false to decline the password
     *
     * @throws PasswordValidatorException to decline the password and add a error message to the password widget
     */
    public function validate(ValidationContext $context): bool;
}
