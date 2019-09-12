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

namespace Terminal42\PasswordValidationBundle\Validation\Validator;

use Contao\StringUtil;
use Contao\System;
use Symfony\Component\Validator\Exception\ValidatorException;
use Terminal42\PasswordValidationBundle\Validation\PasswordValidatorInterface;
use Terminal42\PasswordValidationBundle\Validation\ValidationConfiguration;
use Terminal42\PasswordValidationBundle\Validation\ValidationContext;

final class RequiredCharacters implements PasswordValidatorInterface
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
        $require       = $configuration['require'];
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
                    $errors[] = new ValidatorException(
                        sprintf($this->translate('required.other'), $minimum, $configuration['other_chars'])
                    );
                    continue;
                }

                $errors[] = new ValidatorException(sprintf($this->translate('required.'.$category), $minimum));
            }
        }

        if (\count($errors) > 1) {
            throw new ValidatorException(
                sprintf(
                    $this->translate('required.summary'),
                    $require['uppercase'],
                    $require['lowercase'],
                    $require['numbers'],
                    $require['other'],
                    $configuration['other_chars']
                )
            );
        }

        if (\count($errors) > 0) {
            throw array_pop($errors);
        }

        return true;
    }

    private function countRequirement(string $category, string $string, ValidationContext $context): ?int
    {
        switch ($category) {
            case 'lowercase':
                $uppercase = mb_strtoupper($string);

                return \strlen($uppercase) - similar_text($string, $uppercase);

            case 'uppercase':
                $lowercase = mb_strtolower($string);

                return \strlen($lowercase) - similar_text($string, $lowercase);

            case 'numbers':
                return \strlen(preg_replace('/\D+/', '', $string));

            case 'other':
                $chars = $this->getRequiredOtherCharactersForRegexp($context);
                if (null === $chars) {
                    return null;
                }

                return \strlen(preg_replace('/[^'.$chars.']+/', '', $string));

            default:
                return null;
        }
    }

    private function getRequiredOtherCharactersForRegexp(ValidationContext $context): ?string
    {
        $config = $this->configuration->getConfiguration($context->getUserEntity());
        $chars  = $config['other_chars'];
        if (!$chars) {
            return null;
        }

        $return = '';
        foreach (array_unique(preg_split('//u', $chars, -1, PREG_SPLIT_NO_EMPTY)) as $char) {
            $return .= '\\'.$char;
        }

        return $return;
    }

    private function translate(string $key)
    {
        System::loadLanguageFile('exception');

        [$key1, $key2] = StringUtil::trimsplit('.', $key);

        return $GLOBALS['TL_LANG']['XPT']['passwordValidation'][$key1][$key2];
    }
}
