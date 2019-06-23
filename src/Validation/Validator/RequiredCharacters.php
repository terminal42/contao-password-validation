<?php


namespace Terminal42\PasswordValidationBundle\Validation\Validator;

use Contao\StringUtil;
use Contao\System;
use Symfony\Component\Validator\Exception\ValidatorException;
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

        foreach ($require as $category => $minimum) {
            if (!$minimum) {
                continue;
            }

            $actual = $this->countRequirement($category, $password, $context);
            if (null === $actual) {
                continue;
            }

            if ($actual < $minimum) {
                throw new ValidatorException(sprintf($this->translate('required.' . $category), $minimum));
            }
        }

        return true;
    }

    private function countRequirement(string $category, string $string, ValidationContext $context): ?int
    {
        switch ($category) {
            case 'lowercase':
                $uppercase = mb_strtoupper($string);

                return strlen($uppercase) - similar_text($string, $uppercase);

            case 'uppercase':
                $lowercase = mb_strtolower($string);

                return strlen($lowercase) - similar_text($string, $lowercase);

            case 'numbers':
                return strlen(preg_replace('/\D+/', '', $string));

            case 'other':
                $chars = $this->getRequiredOtherCharactersForRegexp($context);
                if (null === $chars) {
                    return null;
                }

                return strlen(preg_replace('/[^' . $chars . ']+/', '', $string));

            default:
                return null;
        }
    }

    private function getRequiredOtherCharactersForRegexp(ValidationContext $context): string
    {
        $configuration = $this->configuration->getConfiguration($context->getUserEntity());
        $chars         = $configuration['other_chars'];
        if (!$chars) {
            return null;
        }

        $return = '';
        foreach (str_split($chars) as $char) {
            $return .= '\\' . $char;
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
