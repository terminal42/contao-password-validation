<?php


namespace Terminal42\PasswordValidationBundle\EventListener;

use Contao\FrontendUser;
use Contao\Input;
use Contao\MemberModel;
use Contao\PageModel;
use Message;
use Terminal42\PasswordValidationBundle\Validation\ValidationConfiguration;


/**
 * This class adds a warning if some root pages lack of a configured password-change page.
 */
final class NoPwChangePageWarning
{

    private $configuration;

    public function __construct(ValidationConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function onGetSystemMessages(): string
    {
        $needsPwChangePage = false;
        if ($this->configuration->hasConfiguration(FrontendUser::class)) {
            $configuration = $this->configuration->getConfiguration(FrontendUser::class);
            if ($configuration['change_days']) {
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

        if ($needsPwChangePage && null !== $rootPages) {
            return '<p class="tl_error">' . $this->translate('noPwChangePage') . '</p>';
        }

        return '';
    }

    public function tlPageShowWarning(): void
    {
        if (Input::get('act')) {
            return;
        }

        Message::addRaw($this->onGetSystemMessages());
    }

    private function translate(string $key)
    {
        return $GLOBALS['TL_LANG']['ERR']['passwordValidation'][$key];
    }
}
