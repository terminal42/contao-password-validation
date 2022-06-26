<?php

declare(strict_types=1);

/*
 * This file is part of terminal42/contao-password-validation.
 *
 * (c) terminal42 gmbh <https://terminal42.ch>
 *
 * @license MIT
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
