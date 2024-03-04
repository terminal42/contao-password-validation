<?php

declare(strict_types=1);

namespace Terminal42\PasswordValidationBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Database\Result;
use Contao\MemberModel;

#[AsHook('setNewPassword')]
final class SetNewPasswordListener
{
    /**
     * @param MemberModel|Result|object $member
     */
    public function __invoke($member): void
    {
        if ($member instanceof MemberModel) {
            // We only want to reset "pwChange" if the user changed their password with the
            // change-password module. If this hook is called within the save_callback
            // (backend mask), $member is an instance of Database\Result.
            $member->pwChange = '';
            $member->save();
        }
    }
}
