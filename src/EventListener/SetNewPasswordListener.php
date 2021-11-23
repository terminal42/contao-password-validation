<?php

declare(strict_types=1);

/*
 * Password Validation Bundle for Contao Open Source CMS.
 *
 * @copyright  Copyright (c) 2021, terminal42 gmbh
 * @author     terminal42 <https://terminal42.ch>
 * @license    MIT
 * @link       http://github.com/terminal42/contao-password-validation
 */

namespace Terminal42\PasswordValidationBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\MemberModel;

/**
 * @Hook("setNewPassword")
 */
final class SetNewPasswordListener
{
    public function __invoke($member): void
    {
        if ($member instanceof MemberModel) {
            // We only want to reset "pwChange" if the user changed their password with the change-password module.
            // If this hook is called within the save_callback (backend mask), $member is an instance of Database\Result.
            $member->pwChange = '';
            $member->save();
        }
    }
}
