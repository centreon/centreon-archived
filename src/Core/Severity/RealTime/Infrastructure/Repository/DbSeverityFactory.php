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

namespace Core\Severity\RealTime\Infrastructure\Repository;

use Core\Domain\RealTime\Model\Icon;
use Core\Severity\RealTime\Domain\Model\Severity;

class DbSeverityFactory
{
    /**
     * @param array<string,int|string|null> $record
     * @return Severity
     */
    public static function createFromRecord(array $record): Severity
    {
        /** @var string|null */
        $iconName = $record['icon_name'];

        $icon = (new Icon())
            ->setId((int) $record['icon_id'])
            ->setName($iconName)
            ->setUrl($record['icon_directory'] . DIRECTORY_SEPARATOR . $record['icon_path']);

        return new Severity(
            (int) $record['id'],
            (string) $record['name'],
            (int) $record['level'],
            (int) $record['type'],
            $icon
        );
    }
}
