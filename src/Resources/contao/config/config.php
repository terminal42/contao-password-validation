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

use Terminal42\PasswordValidationBundle\Model\PasswordHistory;

$GLOBALS['TL_MODELS']['tl_password_history'] = PasswordHistory::class;
