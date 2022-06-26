<?php

declare(strict_types=1);

/*
 * This file is part of terminal42/contao-password-validation.
 *
 * (c) terminal42 gmbh <https://terminal42.ch>
 *
 * @license MIT
 */

$GLOBALS['TL_DCA']['tl_user']['fields']['password']['eval']['rgxp'] = 'terminal42_password_validation';
