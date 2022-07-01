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
use Symfony\Component\Finder\Finder;

class LegacyReadVersionRepository extends AbstractRepositoryDRB implements ReadVersionRepositoryInterface
{
    use LoggerTrait;

    /**
     * @param Finder $finder
     * @param DatabaseConnection $db
     */
    public function __construct(
        private Finder $finder,
        DatabaseConnection $db,
    ) {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function getCurrentVersion(): ?string
    {
        $currentVersion = null;

        $statement = $this->db->query(
            "SELECT `value` FROM `informations` WHERE `key` = 'version'"
        );
        if ($statement !== false && is_array($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $currentVersion = $result['value'];
        }

        return $currentVersion;
    }

    /**
     * @inheritDoc
     */
    public function getOrderedAvailableUpdates(string $currentVersion): array
    {
        $availableUpdates = $this->getAvailableUpdates($currentVersion);

        return $this->orderUpdates($availableUpdates);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatesUntil(string $version, array $updates): array
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

    /**
     * Get available updates
     *
     * @param string $currentVersion
     * @return string[]
     */
    private function getAvailableUpdates(string $currentVersion): array
    {
        $fileNameVersionRegex = '/Update-(?<version>[a-zA-Z0-9\-\.]+)\.php/';
        $availableUpdates = [];
        $updateFiles = $this->finder->files()
            ->in(__DIR__ . '/../../../../../www/install/php')
            ->name($fileNameVersionRegex);

        foreach ($updateFiles as $updateFile) {
            if (preg_match($fileNameVersionRegex, $updateFile->getFilename(), $matches)) {
                if (version_compare($matches['version'], $currentVersion, '>')) {
                    $availableUpdates[] = $matches['version'];
                }
            }
        }

        dump($availableUpdates);

        return $availableUpdates;
    }

    /**
     * Order updates
     *
     * @param string[] $updates
     * @return string[]
     */
    private function orderUpdates(array $updates): array
    {
        usort($updates, 'version_compare');

        return $updates;
    }
}
