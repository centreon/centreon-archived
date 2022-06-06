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
     * @param array<string, mixed> $data
     * @return Severity
     */
    public static function createFromRecord(array $data): Severity
    {
        $icon = (new Icon())
            ->setName($data['icon_name'])
            ->setUrl($data['icon_url']);

        return new Severity((int) $data['id'], $data['name'], (int) $data['level'], $icon);
    }
}
