<?php

namespace Core\Infrastructure\User\Repository;

use Centreon\Domain\Repository\RepositoryException;
use Core\Domain\User\Model\User;
use Core\Domain\User\Model\UserPassword;

class DbUserFactory
{
    /**
     * @param array<string, mixed> $recordData
     * @return User
     */
    public static function createFromRecord(array $recordData): User
    {
        if (empty($recordData)) {
            throw new RepositoryException(_('User information not found'));
        }
        $userInfos = [
            'passwords' => []
        ];
        foreach ($recordData as $record) {
            $userInfos['contact_id'] = (int) $record['contact_id'];
            $userInfos['contact_alias'] = $record['contact_alias'];
            $userInfos['passwords'][] = new UserPassword(
                (int) $record['contact_id'],
                $record['password'],
                (int) $record['creation_date']
            );
        }

        return new User(
            $userInfos['contact_id'],
            $userInfos['contact_alias'],
            $userInfos['passwords'],
            end($userInfos['passwords'])
        );
    }
}
