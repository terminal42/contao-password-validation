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

/**
 * This class holds arbitrary validators, each getting asked to verify the password input.
 */
final class ValidatorManager
{
    /**
     * @var array<PasswordValidatorInterface>
     */
    private $validators = [];

    public function __construct(iterable $validators)
    {
        $this->validators = $validators instanceof \Traversable ? iterator_to_array($validators) : $validators;
    }

    public function addValidator(PasswordValidatorInterface $validator, string $alias): void
    {
        $this->validators[$alias] = $validator;
    }

    public function getValidatorNames(): array
    {
        return array_keys($this->validators);
    }

    public function getValidator($name): ?PasswordValidatorInterface
    {
        return $this->validators[$name] ?? null;
    }
}
