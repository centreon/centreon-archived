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

namespace Core\Contact\Infrastructure\Repository;

use Centreon\Domain\Log\LoggerTrait;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Contact\Application\Repository\ReadContactGroupRepositoryInterface;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;
use Core\Contact\Domain\Model\ContactGroup;

class DbReadContactGroupRepository extends AbstractRepositoryDRB implements ReadContactGroupRepositoryInterface
{
    use LoggerTrait;

    /**
     * @var SqlRequestParametersTranslator
     */
    private SqlRequestParametersTranslator $sqlRequestTranslator;

    /**
     * @param DatabaseConnection $db
     * @param SqlRequestParametersTranslator $sqlRequestTranslator
     */
    public function __construct(DatabaseConnection $db, SqlRequestParametersTranslator $sqlRequestTranslator)
    {
        $this->db = $db;
        $this->sqlRequestTranslator = $sqlRequestTranslator;
        $this->sqlRequestTranslator
            ->getRequestParameters()
            ->setConcordanceStrictMode(RequestParameters::CONCORDANCE_MODE_STRICT);

        $this->sqlRequestTranslator->setConcordanceArray([
            'id' => 'cg_id',
            'name' => 'cg_name'
        ]);
    }

    /**
     * @inheritDoc
     */
    public function findAll(): array
    {
        $request = "SELECT SQL_CALC_FOUND_ROWS cg_id, cg_name FROM contactgroup";

        // Search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        $request .= $searchRequest !== null
            ? $searchRequest . ' AND '
            : ' WHERE ';

        $request .= "cg_activate = '1' ";

        // Sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $request .= $sortRequest !== null ? $sortRequest : ' ORDER BY cg_id ASC';

        // Pagination
        $request .= $this->sqlRequestTranslator->translatePaginationToSql();

        $statement = $this->db->prepare($request);

        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            /**
             * @var int
             */
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }

        $statement->execute();

        // Set total
        $result = $this->db->query('SELECT FOUND_ROWS()');
        if ($result !== false && ($total = $result->fetchColumn()) !== false) {
            $this->sqlRequestTranslator->getRequestParameters()->setTotal((int) $total);
        }

        $contactGroups = [];
        while ($statement !== false && is_array($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $contactGroups[] = DbContactGroupFactory::createFromRecord($result);
        }

        return $contactGroups;
    }

    /**
     * @inheritDoc
     */
    public function findAllByUserId(int $userId): array
    {
        $request = "SELECT SQL_CALC_FOUND_ROWS cg_id, cg_name FROM contactgroup cg " .
            "INNER JOIN contactgroup_contact_relation ccr " .
            "ON ccr.contactgroup_cg_id = cg.cg_id";

        // Search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        $request .= $searchRequest !== null
            ? $searchRequest . ' AND '
            : ' WHERE ';

        $request .= "ccr.contact_contact_id = :userId AND cg_activate = '1'";

        // Sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $request .= $sortRequest !== null ? $sortRequest : ' ORDER BY cg_id ASC';

        // Pagination
        $request .= $this->sqlRequestTranslator->translatePaginationToSql();

        $statement = $this->db->prepare($request);

        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            /**
             * @var int
             */
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }
        $statement->bindValue(':userId', $userId, \PDO::PARAM_INT);
        $statement->execute();

        // Set total
        $result = $this->db->query('SELECT FOUND_ROWS()');
        if ($result !== false && ($total = $result->fetchColumn()) !== false) {
            $this->sqlRequestTranslator->getRequestParameters()->setTotal((int) $total);
        }

        $contactGroups = [];
        while ($statement !== false && is_array($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $contactGroups[] = DbContactGroupFactory::createFromRecord($result);
        }

        return $contactGroups;
    }

    /**
     * @inheritDoc
     */
    public function find(int $contactGroupId): ?ContactGroup
    {
        $this->debug("Getting Contact Group by id", [
            "contact_group_id" => $contactGroupId
        ]);
        $statement = $this->db->prepare("SELECT cg_id,cg_name FROM contactgroup WHERE cg_id = :contactGroupId");
        $statement->bindValue(':contactGroupId', $contactGroupId, \PDO::PARAM_INT);
        $statement->execute();
        $contactGroup = null;
        if ($statement !== false && $result = $statement->fetch(\PDO::FETCH_ASSOC)) {
            /**
             * @var array<string, string> $result
             */
            $contactGroup = DbContactGroupFactory::createFromRecord($result);
        }
        $this->debug(
            $contactGroup === null  ? "No Contact Group found" : "Contact Group Found",
            [
                "contact_group_id" => $contactGroupId
            ]
        );
        return $contactGroup;
    }

    /**
     * @inheritDoc
     */
    public function findByIds(array $contactGroupIds): array
    {
        $this->debug('Getting Contact Group by Ids', [
            "ids" => implode(", ", $contactGroupIds)
        ]);
        $queryBindValues = [];
        foreach ($contactGroupIds as $contactGroupId) {
            $queryBindValues[':contact_group_' . $contactGroupId] = $contactGroupId;
        }

        if (empty($queryBindValues)) {
            return [];
        }
        $contactGroups = [];
        $boundIds = implode(', ', array_keys($queryBindValues));
        $statement = $this->db->prepare(
            "SELECT cg_id,cg_name FROM contactgroup WHERE cg_id IN ($boundIds)"
        );
        foreach ($queryBindValues as $bindKey => $contactGroupId) {
            $statement->bindValue($bindKey, $contactGroupId, \PDO::PARAM_INT);
        }
        $statement->execute();

        while ($statement !== false && is_array($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $contactGroups[] = DbContactGroupFactory::createFromRecord($result);
        }
        $this->debug('Contact Group found: ' . count($contactGroups));

        return $contactGroups;
    }
}
