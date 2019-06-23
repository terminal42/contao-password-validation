<?php

/*
 * password-validation extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2019, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    MIT
 * @link       http://github.com/terminal42/contao-password-validation
 */

namespace Terminal42\PasswordValidationBundle\Validation\Validator;

use Contao\System;
use Symfony\Component\Validator\Exception\ValidatorException;
use Terminal42\PasswordValidationBundle\Model\PasswordHistory as PasswordHistoryModel;
use Terminal42\PasswordValidationBundle\Validation\ValidationConfiguration;
use Terminal42\PasswordValidationBundle\Validation\ValidationContext;

final class PasswordHistory implements PasswordValidatorInterface
{
    private $configuration;

    public function __construct(ValidationConfiguration $configuration)
    {
        $this->configuration = $configuration;
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
        $userId = $context->getUserId();
        $password = $context->getPassword()->getString();

        $history = PasswordHistoryModel::findHistory($userEntity, $userId, $historyLength);
        if (null === $history) {
            return true;
        }

        /** @var PasswordHistoryModel $log */
        foreach ($history as $log) {
            if (password_verify($password, $log->password)) {
                throw new ValidatorException($this->translate('passwordHistory'));
            }
        }

        return true;
    }

    private function translate(string $key)
    {
        System::loadLanguageFile('exception');

        return $GLOBALS['TL_LANG']['XPT']['passwordValidation'][$key];
    }
}
