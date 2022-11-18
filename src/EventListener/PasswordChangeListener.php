<?php

declare(strict_types=1);

/*
 * This file is part of terminal42/contao-password-validation.
 *
 * (c) terminal42 gmbh <https://terminal42.ch>
 *
 * @license MIT
 */

namespace Terminal42\PasswordValidationBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\User;
use Terminal42\PasswordValidationBundle\Model\PasswordHistory;
use Terminal42\PasswordValidationBundle\Validation\ValidationConfiguration;

/**
 * This listener forces a password change for passwords being too old.
 *
 * @Hook("postLogin")
 */
final class PasswordChangeListener
{
    private $configuration;

    public function __construct(ValidationConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function __invoke(User $user): void
    {
        if (null === $maxDays = $this->getMaxDays($user)) {
            return;
        }

        $userId = (int) $user->id;
        $passwordLog = PasswordHistory::findCurrentLog(\get_class($user), $userId);

        if (null === $passwordLog) {
            $this->forcePasswordChange($user);
        }

        $maxAge = strtotime("-$maxDays days");

        if ($passwordLog->tstamp < $maxAge) {
            $this->forcePasswordChange($user);
        }
    }

    private function getMaxDays(User $user): ?int
    {
        $userEntity = \get_class($user);

        if (false === $this->configuration->hasConfiguration($userEntity)) {
            return null;
        }

        $configuration = $this->configuration->getConfiguration($userEntity);

        $maxDays = $configuration['change_days'] ?? null;

        if (!$maxDays) {
            return null;
        }

        return $maxDays;
    }

    private function forcePasswordChange(User $user): void
    {
        $user->pwChange = 1;
        $user->save();
    }
}
