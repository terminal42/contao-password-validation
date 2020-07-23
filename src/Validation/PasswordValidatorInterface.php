<?php

declare(strict_types=1);

/*
 * Password Validation Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2020, terminal42 gmbh
 * @author     terminal42 <https://terminal42.ch>
 * @license    MIT
 * @link       http://github.com/terminal42/contao-password-validation
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
