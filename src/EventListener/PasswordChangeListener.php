<?php


namespace Terminal42\PasswordValidationBundle\EventListener;

use Contao\User;
use Terminal42\PasswordValidationBundle\Model\PasswordHistory;
use Terminal42\PasswordValidationBundle\Validation\ValidationConfiguration;


/**
 * This listener forces a password change for passwords being too old.
 */
final class PasswordChangeListener
{

    private $configuration;

    public function __construct(ValidationConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function onPostLogin(User $user): void
    {
        if (null === $maxDays = $this->getMaxDays($user)) {
            return;
        }

        $passwordLog = PasswordHistory::findCurrentLog(get_class($user), $user->id);
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
        $userEntity = get_class($user);
        if (false === $this->configuration->hasConfiguration($userEntity)) {
            return null;
        }

        $configuration = $this->configuration->getConfiguration($userEntity);

        $maxDays = $configuration['change_days'];
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
