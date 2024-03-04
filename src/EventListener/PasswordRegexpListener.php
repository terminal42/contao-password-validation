<?php

declare(strict_types=1);

/*
 * This file is part of terminal42/contao-password-validation.
 *
 * (c) terminal42 gmbh <https://terminal42.ch>
 *
 * @license MIT
 */

namespace Terminal42\PasswordValidationBundle\EventListener;

use Contao\BackendUser;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\FrontendUser;
use Contao\Input;
use Contao\ModulePersonalData;
use Contao\ModuleRegistration;
use Contao\Widget;
use ParagonIE\HiddenString\HiddenString;
use Symfony\Component\HttpFoundation\RequestStack;
use Terminal42\PasswordValidationBundle\Exception\PasswordValidatorException;
use Terminal42\PasswordValidationBundle\Validation\ValidationConfiguration;
use Terminal42\PasswordValidationBundle\Validation\ValidationContext;
use Terminal42\PasswordValidationBundle\Validation\ValidatorManager;

/**
 * This listener validates the password input by providing a regexp.
 *
 * @Hook("addCustomRegexp")
 */
final class PasswordRegexpListener
{
    private $validatorManager;

    private $configuration;
    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(ValidatorManager $validatorManager, ValidationConfiguration $configuration, RequestStack $requestStack)
    {
        $this->validatorManager = $validatorManager;
        $this->configuration = $configuration;
        $this->requestStack = $requestStack;
    }

    public function __invoke(string $rgxp, $input, Widget $widget): bool
    {
        if ('terminal42_password_validation' !== $rgxp) {
            return false;
        }

        $dc = $widget->dataContainer;

        if ($dc instanceof ModulePersonalData) {
            $userId = (int) FrontendUser::getInstance()->id;
            $userEntity = FrontendUser::class;
        } elseif ($dc instanceof ModuleRegistration) {
            $userId = null;
            $userEntity = FrontendUser::class;
        } elseif ('FE' === TL_MODE && FE_USER_LOGGED_IN) {
            $userId = (int) FrontendUser::getInstance()->id;
            $userEntity = FrontendUser::class;
        } elseif (
            'FE' === TL_MODE
            && Input::post('FORM_SUBMIT') === $this->requestStack->getSession()->get('setPasswordToken')
            && $widget->currentRecord
        ) {
            // handles ModulePassword in Contao 4.13
            $userId = $widget->currentRecord;
            $userEntity = FrontendUser::class;
        } elseif (null !== $dc) {
            if ('tl_member' === $dc->table) {
                $userId = (int) $dc->id;
                $userEntity = FrontendUser::class;
            } elseif ('tl_user' === $dc->table) {
                $userId = (int) $dc->id;
                $userEntity = BackendUser::class;
            } else {
                return true;
            }
        } else {
            return true;
        }

        if (false === $this->configuration->hasConfiguration($userEntity)) {
            return true;
        }

        $password = new HiddenString($input);
        $context = new ValidationContext($userEntity, $userId, $password);

        foreach ($this->validatorManager->getValidatorNames() as $validatorName) {
            if (null !== $validator = $this->validatorManager->getValidator($validatorName)) {
                try {
                    if (false === $validator->validate($context)) {
                        $widget->addError('Your password does not fit with the requirements.');

                        return true;
                    }
                } catch (PasswordValidatorException $e) {
                    $widget->addError($e->getMessage());

                    return true;
                } catch (\Throwable $e) {
                    // Unhandled exceptions can be dangerous since the plaintext password is passed to this method.
                    // The password would have been available in the stack trace.
                    $widget->addError('An unexpected error occurred.');

                    return true;
                }
            }
        }

        return true;
    }
}
