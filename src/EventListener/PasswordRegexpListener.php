<?php

declare(strict_types=1);

namespace Terminal42\PasswordValidationBundle\EventListener;

use Contao\BackendUser;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\FrontendUser;
use Contao\Input;
use Contao\ModulePersonalData;
use Contao\ModuleRegistration;
use Contao\Widget;
use ParagonIE\HiddenString\HiddenString;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Terminal42\PasswordValidationBundle\Exception\PasswordValidatorException;
use Terminal42\PasswordValidationBundle\Validation\ValidationConfiguration;
use Terminal42\PasswordValidationBundle\Validation\ValidationContext;
use Terminal42\PasswordValidationBundle\Validation\ValidatorManager;

/**
 * This listener validates the password input by providing a regexp.
 */
#[AsHook('addCustomRegexp')]
final class PasswordRegexpListener
{
    public function __construct(
        private readonly ValidatorManager $validatorManager,
        private readonly ValidationConfiguration $configuration,
        private readonly RequestStack $requestStack,
        private readonly ScopeMatcher $scopeMatcher,
        private readonly Security $security,
    ) {
    }

    public function __invoke(string $rgxp, #[\SensitiveParameter] mixed $input, Widget $widget): bool
    {
        if ('terminal42_password_validation' !== $rgxp) {
            return false;
        }

        $dc = $widget->dataContainer;
        $request = $this->requestStack->getCurrentRequest();

        if ($dc instanceof ModulePersonalData) {
            $userId = (int) FrontendUser::getInstance()->id;
            $userEntity = FrontendUser::class;
        } elseif ($dc instanceof ModuleRegistration) {
            $userId = null;
            $userEntity = FrontendUser::class;
        } elseif ($request && $this->scopeMatcher->isFrontendRequest($request) && $this->security->isGranted('ROLE_MEMBER')) {
            $userId = (int) FrontendUser::getInstance()->id;
            $userEntity = FrontendUser::class;
        } elseif (
            $request
            && $this->scopeMatcher->isFrontendRequest($request)
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
                } catch (\Throwable) {
                    // Unhandled exceptions can be dangerous since the plaintext password is passed
                    // to this method. The password would have been available in the stack trace.
                    $widget->addError('An unexpected error occurred.');

                    return true;
                }
            }
        }

        return true;
    }
}
