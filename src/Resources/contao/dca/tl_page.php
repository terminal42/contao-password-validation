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

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\DataContainer\PaletteNotFoundException;

$paletteManipulator = PaletteManipulator::create()
    ->addLegend('pwChangeLegend', 'publish_legend', PaletteManipulator::POSITION_BEFORE)
    ->addField('pwChangePage', 'pwChangeLegend', PaletteManipulator::POSITION_APPEND)
    ->addField('nc_account_disabled', 'pwChangeLegend', PaletteManipulator::POSITION_APPEND)
;

try {
    $paletteManipulator->applyToPalette('root', 'tl_page');
    $paletteManipulator->applyToPalette('rootfallback', 'tl_page');
} catch (PaletteNotFoundException $e) {
}

$GLOBALS['TL_DCA']['tl_page']['config']['onload_callback'][] =
    ['terminal42_password_validation.listener.no_password_change_page_warning', 'tlPageShowWarning'];

$GLOBALS['TL_DCA']['tl_page']['fields']['pwChangePage'] = [
    'label'      => &$GLOBALS['TL_LANG']['tl_page']['pwChangePage'],
    'exclude'    => true,
    'inputType'  => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval'       => ['fieldType' => 'radio', 'tl_class' => 'clr'],
    'sql'        => "int(10) unsigned NOT NULL default '0'",
    'relation'   => ['type' => 'hasOne', 'load' => 'eager'],
];

$GLOBALS['TL_DCA']['tl_page']['fields']['nc_account_disabled'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_page']['nc_account_disabled'],
    'exclude'          => true,
    'inputType'        => 'select',
    'options_callback' => static function () {
        return \Contao\System::getContainer()
            ->get('database_connection')
            ->executeQuery('SELECT id,title FROM tl_nc_notification WHERE type=\'account_disabled\' ORDER BY title')
            ->fetchAll(PDO::FETCH_KEY_PAIR);
    },
    'eval'             => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50 clr'],
    'sql'              => "int(10) unsigned NOT NULL default '0'",
    'relation'         => ['type' => 'hasOne', 'load' => 'lazy', 'table' => 'tl_nc_notification'],
];
