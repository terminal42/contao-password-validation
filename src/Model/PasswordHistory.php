<?php

declare(strict_types=1);

/*
 * This file is part of terminal42/contao-password-validation.
 *
 * (c) terminal42 gmbh <https://terminal42.ch>
 *
 * @license MIT
 */

namespace Terminal42\PasswordValidationBundle\Model;

use Contao\Model;
use Contao\Model\Collection;

final class PasswordHistory extends Model
{
    protected static $strTable = 'tl_password_history';

    public static function addLog(string $entity, int $userId, #[\SensitiveParameter] string $hashedPassword): void
    {
        $log = new self();

        $log->user_id = $userId;
        $log->user_entity = $entity;
        $log->tstamp = time();
        $log->password = $hashedPassword;

        $log->save();
    }

    /**
     * @return Collection<PasswordHistory>|null
     */
    public static function findHistory(string $entity, int $userId, int $length = 10, int $offset = 0): Collection|null
    {
        return self::findBy(
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
                'limit' => $length,
                'offset' => $offset,
                'return' => 'Collection',
            ],
        );
    }

    public static function findCurrentLog(string $entity, int $userId): self|null
    {
        return self::findOneBy(
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
            ],
        );
    }

    public static function clearLog(string $entity, int $userId, int $lengthToKeep = 10): void
    {
        $history = self::findHistory($entity, $userId, 0, $lengthToKeep);

        if (null !== $history) {
            foreach ($history as $log) {
                $log->delete();
            }
        }
    }
}
