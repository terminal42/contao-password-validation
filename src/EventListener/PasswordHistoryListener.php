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

use Contao\BackendUser;
use Contao\Database\Result as DatabaseResult;
use Contao\DataContainer;
use Contao\FrontendUser;
use Contao\MemberModel;
use Terminal42\PasswordValidationBundle\Model\PasswordHistory;
use Terminal42\PasswordValidationBundle\Validation\ValidationConfiguration;

/**
 * This listener adds the new password to the password history.
 */
final class PasswordHistoryListener
{
    private $configuration;

    public function __construct(ValidationConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * This hook is triggered for frontend users exclusively (ModuleChangePassword and save_callback).
     *
     * @param MemberModel|DatabaseResult $member
     * @param string                     $password
     */
    public function onSetNewPassword($member, string $password): void
    {
        if (false === $this->configuration->hasConfiguration(FrontendUser::class)) {
            return;
        }

        $configuration = $this->configuration->getConfiguration(FrontendUser::class);

        $historyLength = (int) $configuration['password_history'];
        if (0 === $historyLength) {
            return;
        }

        if (0 !== strncmp($password, '$2y$', 4)) {
            // This should never be the case.
            $password = password_hash($password, PASSWORD_DEFAULT);
        }

        PasswordHistory::addLog(FrontendUser::class, $member->id, $password);
        PasswordHistory::clearLog(FrontendUser::class, $member->id, $historyLength);
    }

    /**
     * This listener keeps track of the backend user's passwords.
     *
     * @param string        $password
     * @param DataContainer $dc
     *
     * @return string
     */
    public function onBackendSaveCallback(string $password, DataContainer $dc): string
    {
        if (0 !== strncmp($password, '$2y$', 4)) {
            $password = password_hash($password, PASSWORD_DEFAULT);
        }

        if (false === $this->configuration->hasConfiguration(BackendUser::class)) {
            return $password;
        }

        $configuration = $this->configuration->getConfiguration(BackendUser::class);

        $historyLength = (int) $configuration['password_history'];
        if (0 === $historyLength) {
            return $password;
        }

        PasswordHistory::addLog(BackendUser::class, $dc->id, $password);
        PasswordHistory::clearLog(BackendUser::class, $dc->id, $historyLength);

        return $password;
    }
}
