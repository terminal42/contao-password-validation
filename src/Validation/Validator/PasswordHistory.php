<?php

declare(strict_types=1);

namespace Terminal42\PasswordValidationBundle\Validation\Validator;

use Contao\User;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\PasswordValidationBundle\Exception\PasswordValidatorException;
use Terminal42\PasswordValidationBundle\Model\PasswordHistory as PasswordHistoryModel;
use Terminal42\PasswordValidationBundle\Validation\PasswordValidatorInterface;
use Terminal42\PasswordValidationBundle\Validation\ValidationConfiguration;
use Terminal42\PasswordValidationBundle\Validation\ValidationContext;

final class PasswordHistory implements PasswordValidatorInterface
{
    public function __construct(
        private readonly ValidationConfiguration $configuration,
        private readonly PasswordHasherFactoryInterface $encoderFactory,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function validate(ValidationContext $context): bool
    {
        if (false === $this->configuration->hasConfiguration($context->getUserEntity())) {
            return true;
        }

        if (null === $context->getUserId()) {
            return true;
        }

        $configuration = $this->configuration->getConfiguration($context->getUserEntity());
        $historyLength = $configuration['password_history'] ?? null;

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
            if ($this->encoderFactory->getPasswordHasher(User::class)->verify((string) $log->password, $password)) {
                throw new PasswordValidatorException($this->translator->trans('XPT.passwordValidation.passwordHistory', [], 'contao_exception'));
            }
        }

        return true;
    }
}
