<?php

declare(strict_types=1);

/*
 * This file is part of terminal42/contao-password-validation.
 *
 * (c) terminal42 gmbh <https://terminal42.ch>
 *
 * @license MIT
 */

namespace Terminal42\PasswordValidationBundle\Validation\Validator;

use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\PasswordValidationBundle\Exception\PasswordValidatorException;
use Terminal42\PasswordValidationBundle\Validation\PasswordValidatorInterface;
use Terminal42\PasswordValidationBundle\Validation\ValidationConfiguration;
use Terminal42\PasswordValidationBundle\Validation\ValidationContext;

use function Symfony\Component\String\s;

final readonly class RequiredCharacters implements PasswordValidatorInterface
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
        $require = $configuration['require'] ?? null;

        if (!$require) {
            return true;
        }

        $password = $context->getPassword()->getString();

        $errors = [];

        foreach ($require as $category => $minimum) {
            if (!$minimum) {
                continue;
            }

            $actual = $this->countRequirement($category, $password, $context);

            if (null === $actual) {
                continue;
            }

            if ($actual < $minimum) {
                if ('other' === $category) {
                    $errors[] = new PasswordValidatorException(
                        $this->translator->trans(
                            'XPT.passwordValidation.required.other',
                            [$minimum, $configuration['other_chars'] ?? ''],
                            'contao_exception',
                        ),
                    );
                    continue;
                }

                $errors[] = new PasswordValidatorException(
                    $this->translator->trans(
                        'XPT.passwordValidation.required.'.$category,
                        [$minimum],
                        'contao_exception',
                    ),
                );
            }
        }

        if (\count($errors) > 1) {
            throw new PasswordValidatorException($this->translator->trans('XPT.passwordValidation.required.summary', [$require['uppercase'] ?? 0, $require['lowercase'] ?? 0, $require['numbers'] ?? 0, $require['other'] ?? 0, $configuration['other_chars'] ?? ''], 'contao_exception'));
        }

        if (\count($errors) > 0) {
            throw array_pop($errors);
        }

        return true;
    }

    private function countRequirement(string $category, string $string, ValidationContext $context): int|null
    {
        switch ($category) {
            case 'lowercase':
                $uppercase = s($string)->upper()->toString();

                return \strlen($uppercase) - similar_text($string, $uppercase);

            case 'uppercase':
                $lowercase = s($string)->lower()->toString();

                return \strlen($lowercase) - similar_text($string, $lowercase);

            case 'numbers':
                return \strlen((string) preg_replace('/\D+/', '', $string));

            case 'other':
                $chars = $this->getRequiredOtherCharactersForRegexp($context);

                if (null === $chars) {
                    return null;
                }

                return \strlen((string) preg_replace('/[^'.$chars.']+/', '', $string));

            default:
                return null;
        }
    }

    private function getRequiredOtherCharactersForRegexp(ValidationContext $context): string|null
    {
        $config = $this->configuration->getConfiguration($context->getUserEntity());
        $chars = $config['other_chars'] ?? null;

        if (!$chars) {
            return null;
        }

        $return = '';

        foreach (array_unique(preg_split('//u', (string) $chars, -1, PREG_SPLIT_NO_EMPTY)) as $char) {
            $return .= '\\'.$char;
        }

        return $return;
    }
}
