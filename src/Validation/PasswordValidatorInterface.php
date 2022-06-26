<?php

declare(strict_types=1);

/*
 * This file is part of terminal42/contao-password-validation.
 *
 * (c) terminal42 gmbh <https://terminal42.ch>
 *
 * @license MIT
 */

namespace Terminal42\PasswordValidationBundle\Validation;

use Terminal42\PasswordValidationBundle\Exception\PasswordValidatorException;

interface PasswordValidatorInterface
{
    /**
     * @param ValidationContext $context holds the password to verify, the user id and the like
     *
     * @throws PasswordValidatorException to decline the password and add a error message to the password widget
     *
     * @return bool true to accept the password, false to decline the password
     */
    public function validate(ValidationContext $context): bool;
}
