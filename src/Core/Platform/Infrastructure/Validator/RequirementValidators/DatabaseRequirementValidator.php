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

namespace Core\Platform\Infrastructure\Validator\RequirementValidators;

use Centreon\Domain\Log\LoggerTrait;
use Core\Platform\Application\Validator\RequirementValidatorInterface;
use Core\Platform\Infrastructure\Validator\RequirementValidators\DatabaseRequirementValidatorInterface;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\DatabaseConnection;

class DatabaseRequirementValidator extends AbstractRepositoryDRB implements RequirementValidatorInterface
{
    use LoggerTrait;

    /**
     * @var string
     */
    private string $version = '';

    /**
     * @var string
     */
    private string $versionComment = '';

    /**
     * @var DatabaseRequirementValidatorInterface[]
     */
    private $dbRequirementValidators;

    /**
     * @param DatabaseConnection $db
     * @param \Traversable<DatabaseRequirementValidatorInterface> $dbRequirementValidators
     *
     * @throws \Exception
     */
    public function __construct(
        DatabaseConnection $db,
        \Traversable $dbRequirementValidators,
    ) {
        $this->db = $db;

        if (iterator_count($dbRequirementValidators) === 0) {
            throw new \Exception('Database requirement validators not found');
        }
        $this->dbRequirementValidators = iterator_to_array($dbRequirementValidators);
    }

    /**
     * {@inheritDoc}
     *
     * @throws DatabaseRequirementException
     */
    public function validateRequirementOrFail(): void
    {
        $this->initDatabaseVersionInformation();

        foreach ($this->dbRequirementValidators as $dbRequirementValidator) {
            if ($dbRequirementValidator->isValidFor($this->versionComment)) {
                $this->info(
                    'Validating requirement by ' . $dbRequirementValidator::class,
                    [
                        'current_version' => $this->version,
                    ],
                );
                $dbRequirementValidator->validateRequirementOrFail($this->version);
                $this->info('Requirement validated by ' . $dbRequirementValidator::class);
            }
        }
    }

    /**
     * Get database version information
     *
     * @throws DatabaseRequirementException
     */
    private function initDatabaseVersionInformation(): void
    {
        $this->info('Getting database version information');

        try {
            $statement = $this->db->query("SHOW VARIABLES WHERE Variable_name IN ('version', 'version_comment')");
            while ($statement !== false && is_array($row = $statement->fetch(\PDO::FETCH_ASSOC))) {
                if ($row['Variable_name'] === "version") {
                    $this->info('Retrieved DBMS version: ' . $row['Value']);
                    $this->version = $row['Value'];
                } elseif ($row['Variable_name'] === "version_comment") {
                    $this->info('Retrieved DBMS version comment: ' . $row['Value']);
                    $this->versionComment = $row['Value'];
                }
            }
        } catch (\Throwable $ex) {
            $this->error(
                'Error when getting DBMS version from database',
                [
                    'message' => $ex->getMessage(),
                    'trace' => $ex->getTraceAsString(),
                ],
            );
            throw DatabaseRequirementException::errorWhenGettingDatabaseVersion($ex);
        }

        if (empty($this->version) || empty($this->versionComment)) {
            $this->info('Cannot retrieve the database version information');
            throw DatabaseRequirementException::cannotRetrieveVersionInformation();
        }
    }
}
