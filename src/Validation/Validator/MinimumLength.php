<?php

declare(strict_types=1);

namespace Terminal42\PasswordValidationBundle\Validation\Validator;

use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\PasswordValidationBundle\Exception\PasswordValidatorException;
use Terminal42\PasswordValidationBundle\Validation\PasswordValidatorInterface;
use Terminal42\PasswordValidationBundle\Validation\ValidationConfiguration;
use Terminal42\PasswordValidationBundle\Validation\ValidationContext;

final readonly class MinimumLength implements PasswordValidatorInterface
{
    public function __construct(
        private ValidationConfiguration $configuration,
        private TranslatorInterface $translator,
    ) {
    }

    public function validate(ValidationContext $context): bool
    {
        if (false === $this->configuration->hasConfiguration($context->getUserEntity())) {
            return true;
        }

        $configuration = $this->configuration->getConfiguration($context->getUserEntity());
        $minimumLength = $configuration['min_length'] ?? null;

        if (!$minimumLength) {
            return true;
        }

        $password = $context->getPassword()->getString();

        if (\strlen($password) < $minimumLength) {
            throw new PasswordValidatorException($this->translator->trans('XPT.passwordValidation.minLength', [$minimumLength], 'contao_exception'));
        }

        return true;
    }
}
