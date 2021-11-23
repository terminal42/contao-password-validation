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

namespace Terminal42\PasswordValidationBundle\Model;

use Contao\Model;

final class PasswordHistory extends Model
{
    protected static $strTable = 'tl_password_history';

    public static function addLog(string $entity, int $userId, string $hashedPassword): void
    {
        $log = new self();

        $log->user_id     = $userId;
        $log->user_entity = $entity;
        $log->tstamp      = time();
        $log->password    = $hashedPassword;

        $log->save();
    }

    public static function findHistory(
        string $entity,
        int $userId,
        int $length = 10,
        int $offset = 0
    ): ?Model\Collection {
        return static::findBy(
            [
                'user_entity=?',
                'user_id=?',
            ],
            [
                $entity,
                $userId,
            ],
            [
                'order'  => 'tstamp DESC',
                'limit'  => $length,
                'offset' => $offset,
                'return' => 'Collection',
            ]
        );
    }

    public static function findCurrentLog(string $entity, int $userId): ?Model
    {
        return static::findOneBy(
            [
                'user_entity=?',
                'user_id=?',
            ],
            [
                $entity,
                $userId,
            ],
            [
                'order' => 'tstamp DESC',
            ]
        );
    }

    public static function clearLog(string $entity, int $userId, int $lengthToKeep = 10): void
    {
        $history = self::findHistory($entity, $userId, 0, $lengthToKeep);
        if (null !== $history) {
            /** @var PasswordHistory $log */
            foreach ($history as $log) {
                $log->delete();
            }
        }
    }
}
