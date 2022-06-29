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

namespace Core\Platform\Infrastructure\Repository;

use Centreon\Domain\Log\LoggerTrait;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Platform\Application\Repository\ReadVersionRepositoryInterface;

class DbReadVersionRepository extends AbstractRepositoryDRB implements ReadVersionRepositoryInterface
{
    use LoggerTrait;

    /**
     * @inheritDoc
     */
    public function getAvailableUpdates(): array
    {
        return [];
    }

    /**
     * filter updates which are anterior to given version
     *
     * @param string $version
     * @param string[] $updates
     * @return array
     *
     * @throws \Exception
     */
    public function filterUpdatesUntil(string $version, array $updates): array
    {
        $filteredUpdates = [];
        foreach ($updates as $update) {
            $filteredUpdates[] = $update;
            if ($update === $version) {
                return $filteredUpdates;
            }
        }

        $errorMessage = "Update to $version is not available";
        $this->error(
            $errorMessage,
            ['available_versions' => implode(', ', $updates)],
        );

        throw new \Exception($errorMessage);
    }
}
