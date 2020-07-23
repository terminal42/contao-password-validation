<?php

declare(strict_types=1);

/*
 * Password Validation Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2019, terminal42 gmbh
 * @author     terminal42 <https://terminal42.ch>
 * @license    MIT
 * @link       http://github.com/terminal42/contao-password-validation
 */

namespace Terminal42\PasswordValidationBundle\EventListener;

use Contao\BackendUser;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FrontendUser;
use Contao\Message;
use Contao\PageModel;
use Contao\User;
use NotificationCenter\Model\Notification;
use Terminal42\PasswordValidationBundle\Validation\ValidationConfiguration;

final class InvalidAttemptsListener
{
    private $configuration;
    private $framework;

    public function __construct(ValidationConfiguration $configuration, ContaoFramework $framework)
    {
        $this->configuration = $configuration;
        $this->framework     = $framework;
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

        if ($user instanceof BackendUser && $user->isAdmin) {
            return false;
        }

        // Check for <=1 because the loginCount will be decreased after this hook call.
        if ($user->loginCount <= 1) {
            // Disable the account
            $user->disable = time();
            // Reset the login count as we do not want the default routine to lock the account.
            // No need to save as the user will be saved nonetheless.
            ++$user->loginCount;

            Message::addError($this->translate('accountDisabled'));

            if (null !== $notification = $this->getNotification($user)) {
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
        if (null === $maximumAttempts = $this->getMaximumInvalidAttempts(\get_class($user))) {
            return;
        }

        $user->loginCount = $maximumAttempts;
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

    private function getNotification(User $user): ?Notification
    {
        /** @var PageModel $adapter */
        $adapter = $this->framework->getAdapter(PageModel::class);

        if (($user instanceof FrontendUser) && null !== $rootPage = $adapter->findByPk($GLOBALS['objPage']->rootId)) {
            $notification = Notification::findByPk($rootPage->nc_account_disabled);
        }

        $configuration = $this->configuration->getConfiguration(\get_class($user));
        if (null === $notification && $notificationId = $configuration['nc_account_disabled']) {
            $notification = Notification::findByPk($notificationId);
        }

        if (null === $notification) {
            $notification = Notification::findOneBy('type', 'account_disabled');
        }

        return $notification;
    }

    private function getNotificationTokens(string $username, User $user): array
    {
        $tokens = [];

        $tokens['user_class'] = \get_class($user);
        $tokens['username']   = $username;

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
