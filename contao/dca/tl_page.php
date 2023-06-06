<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\DataContainer\PaletteNotFoundException;

$paletteManipulator = PaletteManipulator::create()
    ->addLegend('pwChangeLegend', 'publish_legend', PaletteManipulator::POSITION_BEFORE)
    ->addField('pwChangePage', 'pwChangeLegend', PaletteManipulator::POSITION_APPEND)
;

try {
    $paletteManipulator->applyToPalette('root', 'tl_page');
    $paletteManipulator->applyToPalette('rootfallback', 'tl_page');
} catch (PaletteNotFoundException $e) {
}

$GLOBALS['TL_DCA']['tl_page']['fields']['pwChangePage'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_page']['pwChangePage'],
    'exclude' => true,
    'inputType' => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval' => ['fieldType' => 'radio', 'tl_class' => 'clr'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
    'relation' => ['type' => 'hasOne', 'load' => 'eager'],
];
