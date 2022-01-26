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

namespace Core\Infrastructure\RealTime\Repository\Icon;

use Core\Domain\RealTime\Model\Icon;

class DbIconFactory
{
    /**
     * @param array<string, string|null> $data
     * @return Icon|null
     */
    public static function createFromRecord(array $data): ?Icon
    {
        if (
            $data['icon_name'] === null
            && $data['icon_url'] === null
        ) {
            return null;
        }

        return (new Icon())
            ->setName($data['icon_name'])
            ->setUrl($data['icon_url']);
    }
}
