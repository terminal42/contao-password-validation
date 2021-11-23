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

namespace Terminal42\PasswordValidationBundle\Validation\Validator;

use Contao\System;
use Contao\User;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Terminal42\PasswordValidationBundle\Exception\PasswordValidatorException;
use Terminal42\PasswordValidationBundle\Model\PasswordHistory as PasswordHistoryModel;
use Terminal42\PasswordValidationBundle\Validation\PasswordValidatorInterface;
use Terminal42\PasswordValidationBundle\Validation\ValidationConfiguration;
use Terminal42\PasswordValidationBundle\Validation\ValidationContext;

final class PasswordHistory implements PasswordValidatorInterface
{
    private $configuration;

    private $encoderFactory;

    public function __construct(ValidationConfiguration $configuration, EncoderFactoryInterface $encoderFactory)
    {
        $this->configuration  = $configuration;
        $this->encoderFactory = $encoderFactory;
    }

    public function validate(ValidationContext $context): bool
    {
        if (false === $this->configuration->hasConfiguration($context->getUserEntity())) {
            return true;
        }

        $configuration = $this->configuration->getConfiguration($context->getUserEntity());
        $historyLength = $configuration['password_history'];
        if (!$historyLength) {
            return true;
        }

        $userEntity = $context->getUserEntity();
        $userId     = $context->getUserId();
        $password   = $context->getPassword()->getString();

        $history = PasswordHistoryModel::findHistory($userEntity, $userId, $historyLength);
        if (null === $history) {
            return true;
        }

        /** @var PasswordHistoryModel $log */
        foreach ($history as $log) {
            if ($this->verifyPassword((string) $log->password, $password)) {
                throw new PasswordValidatorException($this->translate('passwordHistory'));
            }
        }

        return true;
    }

    private function verifyPassword(string $hashedPassword, string $password)
    {
        if (version_compare(VERSION, '4.8', '>=')) {
            return $this->encoderFactory->getEncoder(User::class)->isPasswordValid($hashedPassword, $password, null);
        }

        return password_verify($password, $hashedPassword);
    }

    private function translate(string $key)
    {
        System::loadLanguageFile('exception');

        return $GLOBALS['TL_LANG']['XPT']['passwordValidation'][$key];
    }
}
