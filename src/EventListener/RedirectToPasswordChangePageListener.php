<?php

declare(strict_types=1);

/*
 * Password Validation Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2020, terminal42 gmbh
 * @author     terminal42 <https://terminal42.ch>
 * @license    MIT
 * @link       http://github.com/terminal42/contao-password-validation
 */

namespace Terminal42\PasswordValidationBundle\EventListener;

use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\FrontendUser;
use Contao\PageModel;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class RedirectToPasswordChangePageListener
{
    private $framework;

    private $tokenStorage;

    private $authenticationTrustResolver;

    private $scopeMatcher;

    public function __construct(
        ContaoFramework $framework,
        TokenStorageInterface $tokenStorage,
        AuthenticationTrustResolverInterface $authenticationTrustResolver,
        ScopeMatcher $scopeMatcher
    ) {
        $this->framework                   = $framework;
        $this->tokenStorage                = $tokenStorage;
        $this->authenticationTrustResolver = $authenticationTrustResolver;
        $this->scopeMatcher                = $scopeMatcher;
    }

    public function __invoke(RequestEvent $event): void
    {
        if (!$this->scopeMatcher->isFrontendMasterRequest($event)) {
            return;
        }

        $token = $this->tokenStorage->getToken();
        if (null === $token || $this->authenticationTrustResolver->isAnonymous($token)) {
            return;
        }

        $request = $event->getRequest();
        $page = $request->attributes->get('pageModel');

        // Check if actual page is available
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

        if (!$user->pwChange) {
            return;
        }

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
        throw new RedirectResponseException($pwChangePage->getAbsoluteUrl());
    }
}
