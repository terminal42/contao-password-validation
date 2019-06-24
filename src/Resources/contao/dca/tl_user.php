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

$GLOBALS['TL_DCA']['tl_user']['fields']['password']['eval']['rgxp']    = 'terminal42_password_validation';
$GLOBALS['TL_DCA']['tl_user']['fields']['password']['save_callback'][] =
    ['terminal42_password_validation.listener.history_log', 'onBackendSaveCallback'];
