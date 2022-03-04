<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */
declare(strict_types=1);

namespace Core\Infrastructure\Security\User\Repository;

use Core\Domain\Security\User\Model\User;
use Core\Domain\Security\User\Model\UserPassword;
use Core\Infrastructure\Common\Repository\DbFactoryUtilitiesTrait;

class DbUserFactory
{
    use DbFactoryUtilitiesTrait;

    /**
     * @param array<string, mixed> $recordData
     * @return User|null
     */
    public static function createFromRecord(array $recordData): ?User
    {
        if (empty($recordData)) {
            return null;
        }

        $userInfos = [
            'passwords' => []
        ];
        foreach ($recordData as $record) {
            $userInfos['contact_id'] = (int) $record['contact_id'];
            $userInfos['contact_alias'] = $record['contact_alias'];
            $userInfos['login_attempts'] = self::getIntOrNull($record['login_attempts']);
            $userInfos['blocking_time'] = self::createDateTimeImmutableFromTimestamp($record['blocking_time']);
            $userInfos['passwords'][] = new UserPassword(
                (int) $record['contact_id'],
                $record['password'],
                (new \DateTimeImmutable())->setTimestamp((int) $record['creation_date']),
            );
        }

        return new User(
            $userInfos['contact_id'],
            $userInfos['contact_alias'],
            $userInfos['passwords'],
            end($userInfos['passwords']),
            $userInfos['login_attempts'],
            $userInfos['blocking_time'],
        );
    }
}
