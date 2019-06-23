<?php

/*
 * password-validation extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2019, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    MIT
 * @link       http://github.com/terminal42/contao-password-validation
 */

namespace Terminal42\PasswordValidationBundle\EventListener;

use Contao\Message;
use Contao\User;
use NotificationCenter\Model\Notification;
use Terminal42\PasswordValidationBundle\Validation\ValidationConfiguration;

final class InvalidAttemptsListener
{
    private $configuration;

    public function __construct(ValidationConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Disable account if maximum invalid attempts reached.
     *
     * @param string $username
     * @param string $password
     * @param User   $user
     *
     * @return bool Never return true as it would authenticate the user!
     */
    public function onCheckCredentials(string $username, string $password, User $user): bool
    {
        if (null === $this->getMaximumInvalidAttempts(\get_class($user))) {
            return false;
        }

        // Check for <2 because the loginCount will be decreased after this hook call.
        if ($user->loginCount < 2) {
            // Disable the account
            $user->disable = time();
            // Reset the login count as we do not want the default routine to lock the account.
            // No need to save as the user will be saved nonetheless.
            ++$user->loginCount;

            Message::addError($this->translate('accountDisabled'));

            /** @var Notification $notification */
            // TODO add field to tl_page (root pages) to configure the message. If none found or in the backend, fall back to this one.
            $notification = Notification::findOneBy('type', 'account_disabled');
            if (null !== $notification) {
                $notification->send($this->getNotificationTokens($username, $user));
            }
        }

        return false;
    }

    /**
     * Set loginCount to maximum allowed attempts after successful login.
     *
     * @param User $user
     */
    public function onPostLogin(User $user): void
    {
        $user->loginCount = $this->getMaximumInvalidAttempts(\get_class($user));
        $user->save();
    }

    private function getMaximumInvalidAttempts(string $userEntity): ?int
    {
        if (false === $this->configuration->hasConfiguration($userEntity)) {
            return null;
        }

        $configuration = $this->configuration->getConfiguration($userEntity);

        if ($invalidAttempts = $configuration['invalid_attempts']) {
            return $invalidAttempts;
        }

        return null;
    }

    private function getNotificationTokens(string $username, User $user): array
    {
        $tokens = [];

        $tokens['username'] = $username;
        foreach ($user->getData() as $k => $v) {
            $tokens['user_'.$k] = $v;
        }

        $tokens['admin_email'] = $GLOBALS['TL_ADMIN_EMAIL'];

        return $tokens;
    }

    private function translate(string $key)
    {
        return $GLOBALS['TL_LANG']['ERR']['passwordValidation'][$key];
    }
}
