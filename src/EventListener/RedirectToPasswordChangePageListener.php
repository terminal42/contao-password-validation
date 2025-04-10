<?php

declare(strict_types=1);

namespace Terminal42\PasswordValidationBundle\EventListener;

use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\FrontendUser;
use Contao\PageModel;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final readonly class RedirectToPasswordChangePageListener
{
    public function __construct(
        private ContaoFramework $framework,
        private TokenStorageInterface $tokenStorage,
        private ScopeMatcher $scopeMatcher,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        if (!$this->scopeMatcher->isFrontendMainRequest($event)) {
            return;
        }

        $request = $event->getRequest();
        $user = $this->tokenStorage->getToken()?->getUser();
        $page = $request->attributes->get('pageModel');

        if (!$page instanceof PageModel || !$user instanceof FrontendUser || !$user->pwChange) {
            return;
        }

        /** @var PageModel $adapter */
        $adapter = $this->framework->getAdapter(PageModel::class);
        $rootPage = $adapter->findById($page->rootId);

        // Search for password-change page
        $pwChangePage = $adapter->findPublishedById($rootPage->pwChangePage ?? 0);

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
