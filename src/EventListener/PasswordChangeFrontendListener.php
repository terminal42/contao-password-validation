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

namespace Terminal42\PasswordValidationBundle\EventListener;

use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FrontendUser;
use Contao\MemberModel;
use Contao\PageModel;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class PasswordChangeFrontendListener
{
    private $framework;

    private $tokenStorage;

    private $authenticationTrustResolver;

    public function __construct(
        ContaoFramework $framework,
        TokenStorageInterface $tokenStorage,
        AuthenticationTrustResolverInterface $authenticationTrustResolver
    ) {
        $this->framework                   = $framework;
        $this->tokenStorage                = $tokenStorage;
        $this->authenticationTrustResolver = $authenticationTrustResolver;
    }

    public function onKernelRequest(GetResponseEvent $event): void
    {
        $token = $this->tokenStorage->getToken();

        if (null === $token || $this->authenticationTrustResolver->isAnonymous($token)) {
            return;
        }

        $this->framework->initialize();

        $page = $GLOBALS['objPage'];
        if (!$page instanceof PageModel) {
            return;
        }

        $user = $token->getUser();
        if (!$user instanceof FrontendUser) {
            return;
        }

        /** @var PageModel $adapter */
        $adapter  = $this->framework->getAdapter(PageModel::class);
        $rootPage = $adapter->findByPk($page->rootId);

        if ($user->pwChange) {
            // Search for password-change page
            $pwChangePage = $adapter->findPublishedById($rootPage->pwChangePage);

            if (!$pwChangePage instanceof PageModel) {
                throw new PageNotFoundException('No password-change page found.');
            }

            // Password-change page found, quit
            if ($page->id === $pwChangePage->id) {
                return;
            }

            // Redirect to password-change page
            $event->setResponse(new RedirectResponse($pwChangePage->getAbsoluteUrl()));

            return;
        }
    }

    public function onSetNewPassword($member): void
    {
        if ($member instanceof MemberModel) {
            // We only want to reset "pwChange" if the user changed their password with the change-password module.
            // If this hook is called within the save_callback (backend mask), $member is an instance of Database\Result.
            $member->pwChange = '';
            $member->save();
        }
    }
}
