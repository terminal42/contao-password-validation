<?php

/*
 * password-validation extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2019, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    MIT
 * @link       http://github.com/terminal42/contao-password-validation
 */

namespace Terminal42\PasswordValidationBundle\Validation\Validator;

use Symfony\Component\Validator\Exception\ValidatorException;
use Terminal42\PasswordValidationBundle\Validation\ValidationContext;

interface PasswordValidatorInterface
{
    /**
     * @param ValidationContext $context the validation context holds the password to verify, the user id and the like
     *
     * @throws ValidatorException
     *
     * @return bool True to accept the password, false to decline the password. Throw a ValidatorException to add a
     *              detailed message.
     */
    public function validate(ValidationContext $context): bool;
}
