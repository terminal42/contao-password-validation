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
use Contao\Database\Result as DatabaseResult;
use Contao\DataContainer;
use Contao\FrontendUser;
use Contao\MemberModel;
use Contao\User;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Terminal42\PasswordValidationBundle\Model\PasswordHistory;
use Terminal42\PasswordValidationBundle\Validation\ValidationConfiguration;

/**
 * This listener adds the new password to the password history.
 */
final class PasswordHistoryListener
{
    private $configuration;

    private $encoderFactory;

    public function __construct(ValidationConfiguration $configuration, EncoderFactoryInterface $encoderFactory)
    {
        $this->configuration  = $configuration;
        $this->encoderFactory = $encoderFactory;
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

        $userId = (int) $member->id;
        PasswordHistory::addLog(FrontendUser::class, $userId, $password);
        PasswordHistory::clearLog(FrontendUser::class, $userId, $historyLength);
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
        if (false === $this->configuration->hasConfiguration(BackendUser::class)) {
            return $password;
        }

        $configuration = $this->configuration->getConfiguration(BackendUser::class);
        if (!$this->isHashedPassword($password)) {
            $password = $this->hashPassword($password);
        }

        $historyLength = (int) $configuration['password_history'];
        if (0 === $historyLength) {
            return $password;
        }

        $userId = (int) $dc->id;
        PasswordHistory::addLog(BackendUser::class, $userId, $password);
        PasswordHistory::clearLog(BackendUser::class, $userId, $historyLength);

        return $password;
    }

    private function isHashedPassword(string $password): bool
    {
        return 0 !== password_get_info($password)['algo'];
    }

    private function hashPassword(string $password): string
    {
        if (version_compare(VERSION, '4.8', '>=')) {
            return $this->encoderFactory->getEncoder(User::class)->encodePassword($password, null);
        }

        return password_hash($password, PASSWORD_DEFAULT);
    }
}
