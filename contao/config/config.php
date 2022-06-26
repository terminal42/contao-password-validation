<?php

declare(strict_types=1);

/*
 * This file is part of terminal42/contao-password-validation.
 *
 * (c) terminal42 gmbh <https://terminal42.ch>
 *
 * @license MIT
 */

use Terminal42\PasswordValidationBundle\Model\PasswordHistory;

$GLOBALS['TL_MODELS']['tl_password_history'] = PasswordHistory::class;
