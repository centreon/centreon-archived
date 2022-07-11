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

namespace Core\Platform\Infrastructure\Repository\RequirementProviders;

use Centreon\Domain\Log\LoggerTrait;
use Core\Platform\Application\Repository\RequirementProviderRepositoryInterface;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Domain\VersionHelper;

class MariaDbRequirementProviderRepository extends AbstractRepositoryDRB implements
    RequirementProviderRepositoryInterface
{
    use LoggerTrait;

    /**
     * @param string $requiredMariaDbMinVersion
     * @param DatabaseConnection $db
     */
    public function __construct(
        private string $requiredMariaDbMinVersion,
        DatabaseConnection $db,
    ) {
        $this->db = $db;
    }

    /**
     * {@inheritDoc}
     *
     * @throws MariaDbRequirementException
     */
    public function validateRequirementOrFail(): void
    {
        $currentMariaDBVersion = $this->getMariaDBVersion();

        if ($currentMariaDBVersion !== null) {
            $currentMariaDBMajorVersion = VersionHelper::regularizeDepthVersion($currentMariaDBVersion, 1);
            $this->info(
                'Comparing current MariaDB version ' . $currentMariaDBMajorVersion
                . ' to minimal required version ' . $this->requiredMariaDbMinVersion
            );
            if (
                VersionHelper::compare($currentMariaDBMajorVersion, $this->requiredMariaDbMinVersion, VersionHelper::LT)
            ) {
                $this->error('MariaDB requirement is not validated');
                throw MariaDbRequirementException::badMariaDbVersion(
                    $this->requiredMariaDbMinVersion,
                    $currentMariaDBVersion,
                );
            }
        }
    }

    /**
    * Get MariaDB version
    * Returns nulls if not found or if MySQL is installed
    *
    * @return string|null
    */
    private function getMariaDBVersion(): ?string
    {
        $this->info('Getting MariaDB version');

        $version = null;
        $dbmsName = null;

        try {
            $statement = $this->db->query("SHOW VARIABLES WHERE Variable_name IN ('version', 'version_comment')");
            while ($statement !== false && is_array($row = $statement->fetch(\PDO::FETCH_ASSOC))) {
                if ($row['Variable_name'] === "version") {
                    $this->info('Retrieved DBMS version: ' . $row['Value']);
                    $version = $row['Value'];
                } elseif ($row['Variable_name'] === "version_comment") {
                    $this->info('Retrieved DBMS version comment: ' . $row['Value']);
                    $dbmsName = $row['Value'];
                }
            }
        } catch (\Throwable $e) {
            $this->error(
                'Error when getting MariaDB version from database',
                [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ],
            );
            throw MariaDbRequirementException::errorWhenGettingMariaDbVersion($e);
        }

        if (strpos($dbmsName, "MariaDB") !== false && $version !== null) {
            $this->info('MariaDB version is ' . $version);
            return $version;
        }

        $this->info('Cannot get MariaDB version. An other DBMS is probably installed');

        return null;
    }
}
