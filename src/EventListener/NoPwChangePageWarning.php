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

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\FrontendUser;
use Contao\Input;
use Contao\MemberModel;
use Contao\Message;
use Contao\PageModel;
use Doctrine\DBAL\DBALException;
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

    /**
     * @Hook("getSystemMessages")
     */
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
            try {
                $members = MemberModel::findBy('pwChange', '1');
            } catch (DBALException $e) {
                $members = null;
            }

            if (null !== $members) {
                $needsPwChangePage = true;
            }
        }

        if (!$needsPwChangePage) {
            return '';
        }

        $rootPages = PageModel::findBy(["tl_page.type='root' AND tl_page.pwChangePage=''"], []);

        if ($needsPwChangePage && null !== $rootPages) {
            return '<p class="tl_error">'.$this->translate('noPwChangePage').'</p>';
        }

        return '';
    }

    /**
     * @Callback(table="tl_page", target="config.onload")
     */
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
