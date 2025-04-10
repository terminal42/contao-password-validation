<?php

declare(strict_types=1);

namespace Terminal42\PasswordValidationBundle\Validation;

/**
 * This class holds arbitrary validators, each getting asked to verify the password input.
 */
final class ValidatorManager
{
    /**
     * @var array<string, PasswordValidatorInterface>
     */
    private array $validators;

    /**
     * @param iterable<PasswordValidatorInterface> $validators
     */
    public function __construct(iterable $validators)
    {
        $this->validators = $validators instanceof \Traversable ? iterator_to_array($validators) : $validators;
    }

    public function addValidator(PasswordValidatorInterface $validator, string $alias): void
    {
        $this->validators[$alias] = $validator;
    }

    /**
     * @return array<string>
     */
    public function getValidatorNames(): array
    {
        return array_keys($this->validators);
    }

    public function getValidator(string $name): PasswordValidatorInterface|null
    {
        return $this->validators[$name] ?? null;
    }
}
