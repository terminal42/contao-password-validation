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

use Contao\BackendUser;
use Contao\FrontendUser;
use ParagonIE\HiddenString\HiddenString;

/**
 * This class holds useful information (e.g. the password to validate) for the validator.
 */
final class ValidationContext
{
    public function __construct(
        private readonly string $userEntity,
        private readonly int|null $userId,
        private readonly HiddenString $password,
    ) {
    }

    public function isFrontendUser(): bool
    {
        return FrontendUser::class === $this->userEntity;
    }

    public function isBackendUser(): bool
    {
        return BackendUser::class === $this->userEntity;
    }

    public function getUserId(): int|null
    {
        return $this->userId;
    }

    public function getPassword(): HiddenString
    {
        return $this->password;
    }

    public function getUserEntity(): string
    {
        return $this->userEntity;
    }
}
