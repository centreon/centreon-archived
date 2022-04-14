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

namespace Core\Infrastructure\Configuration\User\Repository;

use Assert\AssertionFailedException;
use Centreon\Domain\Log\LoggerTrait;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;
use Core\Domain\Configuration\User\Model\User;
use Core\Application\Configuration\User\Repository\ReadUserRepositoryInterface;

class DbReadUserRepository extends AbstractRepositoryDRB implements ReadUserRepositoryInterface
{
    use LoggerTrait;

    /**
     * @param DatabaseConnection $db
     * @param SqlRequestParametersTranslator $sqlRequestTranslator
     */
    public function __construct(
        DatabaseConnection $db,
        private SqlRequestParametersTranslator $sqlRequestTranslator,
    ) {
        $this->db = $db;
        $this->sqlRequestTranslator->setConcordanceArray([
            'id' => 'contact_id',
            'alias' => 'contact_alias',
            'name' => 'contact_name',
            'email' => 'contact_email',
            'provider_name' => 'contact_auth_type',
        ]);
    }

    /**
     * @inheritDoc
     * @throws AssertionFailedException
     */
    public function findAllUsers(): array
    {
        $this->info('Fetching users from database');

        $request =
            "SELECT SQL_CALC_FOUND_ROWS
              contact_id, contact_alias, contact_name, contact_email, contact_admin, contact_theme
            FROM `:db`.contact";

        // Search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        $request .= $searchRequest !== null ? $searchRequest . ' AND ' : ' WHERE ';
        $request .= 'contact_register = 1';

        // Sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $request .= $sortRequest !== null ? $sortRequest : ' ORDER BY contact_id ASC';

        // Pagination
        $request .= $this->sqlRequestTranslator->translatePaginationToSql();

        $statement = $this->db->prepare(
            $this->translateDbName($request)
        );

        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            if (is_array($data)) {
                $type = (int) key($data);
                $value = $data[$type];
                $statement->bindValue($key, $value, $type);
            }
        }

        $statement->execute();

        // Set total
        $result = $this->db->query('SELECT FOUND_ROWS()');
        if ($result !== false && ($total = $result->fetchColumn()) !== false) {
            $this->sqlRequestTranslator->getRequestParameters()->setTotal((int) $total);
        }

        $users = [];

        while ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            /**
             * @var array<string, string> $result
             */
            $users[] = DbUserFactory::createFromRecord($result);
        }

        return $users;
    }

    /**
     * @inheritDoc
     */
    public function findById(int $userId): ?User
    {
        $statement = $this->db->prepare(
            $this->translateDbName('SELECT * FROM `:db`.contact WHERE contact_id = :contact_id')
        );
        $statement->bindValue(':contact_id', $userId, \PDO::PARAM_INT);
        $statement->execute();
        if ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            /**
             * @var array<string, string> $result
             */
            return DbUserFactory::createFromRecord($result);
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function findUserIdsByAliases(array $userAliases): array
    {
        $userIds = [];

        if (empty($userAliases)) {
            return $userIds;
        }

        $this->info('Fetching user ids from database');

        $bindValues = [];
        foreach ($userAliases as $key => $userAlias) {
            $bindValues[':' . $key] = $userAlias;
        }

        $statement = $this->db->prepare(
            $this->translateDbName(
                "SELECT contact_id
                FROM `:db`.contact
                WHERE contact_alias IN (" . implode(',', array_keys($bindValues)) . ")"
            )
        );

        foreach ($bindValues as $key => $value) {
            $statement->bindValue($key, $value, \PDO::PARAM_STR);
        }

        $statement->execute();

        while ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            /**
             * @var array{contact_id: int} $result
             */
            $userIds[] = $result['contact_id'];
        }

        return $userIds;
    }

    /**
     * @inheritDoc
     */
    public function findAvailableThemes(): array
    {
        $statement = $this->db->query(
            'SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_NAME = \'contact\' AND COLUMN_NAME = \'contact_theme\''
        );
        if ($statement != false && $result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            /**
             * @var array<string, string> $result
             */
            if (preg_match_all("/'([^,]+)'/", $result['COLUMN_TYPE'], $match)) {
                /**
                 * @var array<int, string[]> $match
                 */
                return $match[1];
            }
        }
        return [];
    }
}
