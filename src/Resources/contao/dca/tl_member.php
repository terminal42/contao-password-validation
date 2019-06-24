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

$GLOBALS['TL_DCA']['tl_member']['subpalettes']['login'] .= ',pwChange';

$GLOBALS['TL_DCA']['tl_member']['fields']['password']['eval']['rgxp'] = 'terminal42_password_validation';

$GLOBALS['TL_DCA']['tl_member']['fields']['pwChange'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_member']['pwChange'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'filter'    => true,
    'eval'      => ['tl_class' => 'w50 clr'],
    'sql'       => "char(1) NOT NULL default ''",
];
