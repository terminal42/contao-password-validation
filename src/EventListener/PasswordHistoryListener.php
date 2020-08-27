<?php

declare(strict_types=1);

/*
 * Password Validation Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2020, terminal42 gmbh
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
use Contao\StringUtil;
use Contao\User;
use Doctrine\DBAL\Connection;
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

    private $connection;

    public function __construct(
        ValidationConfiguration $configuration,
        EncoderFactoryInterface $encoderFactory,
        Connection $connection
    ) {
        $this->configuration  = $configuration;
        $this->encoderFactory = $encoderFactory;
        $this->connection     = $connection;
    }

    /**
     * This hook is triggered for frontend users exclusively (ModuleChangePassword and save_callback).
     *
     * @param MemberModel|DatabaseResult $member
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

        // When the password history is empty, also save the prior password
        if (null === PasswordHistory::findCurrentLog(FrontendUser::class, $userId)) {
            // Only way is to utilize the versions, because the hook is called post save
            $version = $this->connection->createQueryBuilder()
                ->select('data')
                ->from('tl_version')
                ->where('pid = :user_id')
                ->andWhere('fromTable = :table')
                ->orderBy('version', 'DESC')
                ->setMaxResults(1)
                ->setParameter('user_id', $userId)
                ->setParameter('table', 'tl_member')
                ->execute()
                ->fetchColumn();

            if (false !== $version) {
                $data = StringUtil::deserialize($version);

                PasswordHistory::addLog(FrontendUser::class, $userId, $data['password']);
            }
        }

        PasswordHistory::addLog(FrontendUser::class, $userId, $password);
        PasswordHistory::clearLog(FrontendUser::class, $userId, $historyLength);
    }

    /**
     * This listener keeps track of the backend user's passwords.
     */
    public function onBackendSaveCallback(string $password, DataContainer $dc): string
    {
        if (false === $this->configuration->hasConfiguration(BackendUser::class)) {
            return $password;
        }

        $configuration = $this->configuration->getConfiguration(BackendUser::class);
        $historyLength = (int) $configuration['password_history'];
        if (0 === $historyLength) {
            return $password;
        }

        $hash = $password;
        if (!$this->isHashedPassword($hash)) {
            $hash = $this->hashPassword($hash);
        }

        $userId = (int) $dc->id;
        PasswordHistory::addLog(BackendUser::class, $userId, $hash);
        PasswordHistory::clearLog(BackendUser::class, $userId, $historyLength);

        return $password;
    }

    private function isHashedPassword(string $password): bool
    {
        return (bool) password_get_info($password)['algo'];
    }

    private function hashPassword(string $password): string
    {
        if (version_compare(VERSION, '4.8', '>=')) {
            return $this->encoderFactory->getEncoder(User::class)->encodePassword($password, null);
        }

        return password_hash($password, PASSWORD_DEFAULT);
    }
}
