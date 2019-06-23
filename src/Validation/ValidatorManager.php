<?php


namespace Terminal42\PasswordValidationBundle\Validation;

use Terminal42\PasswordValidationBundle\Validation\Validator\PasswordValidatorInterface;

/**
 * This class holds arbitrary validators, each getting asked to verify the password input.
 */
final class ValidatorManager
{

    /**
     * @var PasswordValidatorInterface[]
     */
    private $validators = [];

    public function addValidator(PasswordValidatorInterface $validator, string $alias): void
    {
        $this->validators[$alias] = $validator;
    }

    public function getValidatorNames(): array
    {
        return \array_keys($this->validators);
    }

    public function getValidator($name): ?PasswordValidatorInterface
    {
        return $this->validators[$name] ?? null;
    }
}
