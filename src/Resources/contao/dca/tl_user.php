<?php

$GLOBALS['TL_DCA']['tl_user']['fields']['password']['eval']['rgxp']    = 'terminal42_password_validation';
$GLOBALS['TL_DCA']['tl_user']['fields']['password']['save_callback'][] =
    ['terminal42_password_validation.listener.history_log', 'onBackendSaveCallback'];
