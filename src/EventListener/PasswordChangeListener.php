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

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\User;
use Terminal42\PasswordValidationBundle\Model\PasswordHistory;
use Terminal42\PasswordValidationBundle\Validation\ValidationConfiguration;

/**
 * This listener forces a password change for passwords being too old.
 */
#[AsHook('postLogin')]
final readonly class PasswordChangeListener
{
    public function __construct(private ValidationConfiguration $configuration)
    {
    }

    public function __invoke(User $user): void
    {
        if (null === $maxDays = $this->getMaxDays($user)) {
            return;
        }

        $userId = (int) $user->id;
        $passwordLog = PasswordHistory::findCurrentLog($user::class, $userId);

        if (!$passwordLog) {
            $this->forcePasswordChange($user);

            return;
        }

        $maxAge = strtotime("-$maxDays days");

        if ($passwordLog->tstamp < $maxAge) {
            $this->forcePasswordChange($user);
        }
    }

    private function getMaxDays(User $user): int|null
    {
        $userEntity = $user::class;

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
        $user->pwChange = true;
        $user->save();
    }
}
