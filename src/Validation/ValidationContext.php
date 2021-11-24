<?php

declare(strict_types=1);

/*
 * Password Validation Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2021, terminal42 gmbh
 * @author     terminal42 <https://terminal42.ch>
 * @license    MIT
 * @link       http://github.com/terminal42/contao-password-validation
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
    private $userEntity;
    private $userId;
    private $password;

    public function __construct(string $userEntity, ?int $userId, HiddenString $password)
    {
        $this->userId     = $userId;
        $this->password   = $password;
        $this->userEntity = $userEntity;
    }

    public function isFrontendUser(): bool
    {
        return FrontendUser::class === $this->userEntity;
    }

    public function isBackendUser(): bool
    {
        return BackendUser::class === $this->userEntity;
    }

    public function getUserId(): ?int
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
