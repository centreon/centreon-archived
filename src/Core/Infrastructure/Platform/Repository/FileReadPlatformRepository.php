<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

namespace Core\Infrastrcture\Platform\Repository;

use Core\Application\Platform\Repository\ReadPlatformRepositoryInterface;

class FileReadPlatformRepository implements ReadPlatformRepositoryInterface
{
    public function __construct(private string $etcDir, private string $installDir)
    {
    }

    /**
     * @inheritdoc
     */
    public function isCentreonWebInstalled(): bool
    {
        if (file_exists($this->etcDir . '/centreon.conf.php')) {
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function isCentreonWebUpgradeAvailable(): bool
    {
        if (is_dir($this->installDir)) {
            return true;
        }

        return false;
    }
}
