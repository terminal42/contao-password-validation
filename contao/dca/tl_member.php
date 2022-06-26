<?php

declare(strict_types=1);

/*
 * This file is part of terminal42/contao-password-validation.
 *
 * (c) terminal42 gmbh <https://terminal42.ch>
 *
 * @license MIT
 */

$GLOBALS['TL_DCA']['tl_member']['subpalettes']['login'] .= ',pwChange';

$GLOBALS['TL_DCA']['tl_member']['fields']['password']['eval']['rgxp'] = 'terminal42_password_validation';

$GLOBALS['TL_DCA']['tl_member']['fields']['pwChange'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_member']['pwChange'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'filter' => true,
    'eval' => ['tl_class' => 'w50 clr'],
    'sql' => "char(1) NOT NULL default ''",
];
