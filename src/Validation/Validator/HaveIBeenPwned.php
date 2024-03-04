<?php

declare(strict_types=1);

namespace Terminal42\PasswordValidationBundle\Validation\Validator;

use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\PasswordValidationBundle\Exception\PasswordValidatorException;
use Terminal42\PasswordValidationBundle\Validation\PasswordValidatorInterface;
use Terminal42\PasswordValidationBundle\Validation\ValidationConfiguration;
use Terminal42\PasswordValidationBundle\Validation\ValidationContext;

final class HaveIBeenPwned implements PasswordValidatorInterface
{
    public function __construct(
        private readonly ValidationConfiguration $configuration,
        private readonly HttpClientInterface $client,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function validate(ValidationContext $context): bool
    {
        if (false === $this->configuration->hasConfiguration($context->getUserEntity())) {
            return true;
        }

        $configuration = $this->configuration->getConfiguration($context->getUserEntity());
        $minDataBreaches = $configuration['haveibeenpwned'] ?? null;

        if (!$minDataBreaches) {
            return true;
        }

        $password = $context->getPassword()->getString();
        $hash = strtoupper(sha1($password));
        $hash05 = substr($hash, 0, 5);

        try {
            $response = $this->client->request('GET', 'https://api.pwnedpasswords.com/range/'.$hash05)->getContent();
        } catch (HttpExceptionInterface) {
            return true;
        }

        $breaches = array_reduce(
            preg_split("/\r\n|\n|\r/", $response),
            static function ($carry, $item) use ($hash05) {
                [$hash35, $quantity] = explode(':', $item);
                $carry[$hash05.$hash35] = $quantity;

                return $carry;
            },
            [],
        );

        if (\array_key_exists($hash, $breaches) && ($quantity = $breaches[$hash]) && $quantity > $minDataBreaches) {
            throw new PasswordValidatorException($this->translator->trans('XPT.passwordValidation.haveibeenpwned', [$quantity], 'contao_exception'));
        }

        return true;
    }
}
