<?php


namespace Terminal42\PasswordValidationBundle\Validation\Validator;

use Symfony\Component\Validator\Exception\ValidatorException;
use Terminal42\PasswordValidationBundle\Validation\ValidationContext;


interface PasswordValidatorInterface
{

    /**
     * @param ValidationContext $context The validation context holds the password to verify, the user id and the like.
     *
     * @return bool True to accept the password, false to decline the password. Throw a ValidatorException to add a
     *              detailed message.
     *
     * @throws ValidatorException
     */
    public function validate(ValidationContext $context): bool;
}
