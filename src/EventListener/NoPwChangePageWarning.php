<?php

declare(strict_types=1);

namespace Terminal42\PasswordValidationBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\FrontendUser;
use Contao\Input;
use Contao\MemberModel;
use Contao\Message;
use Contao\PageModel;
use Symfony\Contracts\Translation\TranslatorInterface;
use Terminal42\PasswordValidationBundle\Validation\ValidationConfiguration;

/**
 * This class adds a warning if some root pages lack of a configured password-change page.
 */
final class NoPwChangePageWarning
{
    public function __construct(
        private readonly ValidationConfiguration $configuration,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[AsHook('getSystemMessages')]
    public function onGetSystemMessages(): string
    {
        $needsPwChangePage = false;

        if ($this->configuration->hasConfiguration(FrontendUser::class)) {
            $configuration = $this->configuration->getConfiguration(FrontendUser::class);

            if ($configuration['change_days'] ?? null) {
                $needsPwChangePage = true;
            }
        }

        if (!$needsPwChangePage) {
            $members = MemberModel::findBy('pwChange', '1');

            if (null !== $members) {
                $needsPwChangePage = true;
            }
        }

        if (!$needsPwChangePage) {
            return '';
        }

        $rootPages = PageModel::findBy(["tl_page.type='root' AND tl_page.pwChangePage=''"], []);

        if (null !== $rootPages) {
            return '<p class="tl_error">'.$this->translator->trans('ERR.passwordValidation.noPwChangePage', [], 'contao_default').'</p>';
        }

        return '';
    }

    #[AsCallback(table: 'tl_page', target: 'config.onload')]
    public function tlPageShowWarning(): void
    {
        if (Input::get('act')) {
            return;
        }

        Message::addRaw($this->onGetSystemMessages());
    }
}
