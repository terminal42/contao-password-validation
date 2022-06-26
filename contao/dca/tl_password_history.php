<?php

declare(strict_types=1);

/*
 * This file is part of terminal42/contao-password-validation.
 *
 * (c) terminal42 gmbh <https://terminal42.ch>
 *
 * @license MIT
 */

$GLOBALS['TL_DCA']['tl_password_history'] = [
    // Config
    'config' => [
        'notDeletable' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'user_id,user_entity' => 'index',
            ],
        ],
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'user_id' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'user_entity' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'password' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
    ],
];
