<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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

namespace Centreon\Infrastructure\Filter;

use Centreon\Domain\Entity\EntityCreator;
use Centreon\Domain\Filter\Interfaces\FilterRepositoryInterface;
use Centreon\Domain\Filter\Filter;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;

/**
 * This class is designed to manage the repository of the monitoring servers
 *
 * @package Centreon\Infrastructure\Filter
 */
class FilterRepositoryRDB extends AbstractRepositoryDRB implements FilterRepositoryInterface
{
    /**
     * @var SqlRequestParametersTranslator
     */
    private $sqlRequestTranslator;

    public function __construct(DatabaseConnection $db)
    {
        $this->db = $db;
    }

    /**
     * Initialized by the dependency injector.
     *
     * @param SqlRequestParametersTranslator $sqlRequestTranslator
     */
    public function setSqlRequestTranslator(SqlRequestParametersTranslator $sqlRequestTranslator): void
    {
        $this->sqlRequestTranslator = $sqlRequestTranslator;
        $this->sqlRequestTranslator
            ->getRequestParameters()
            ->setConcordanceStrictMode(
                RequestParameters::CONCORDANCE_MODE_STRICT
            );
    }

    /**
     * @inheritDoc
     */
    public function addFilter(Filter $filter): void
    {
        $request = $this->translateDbName(
            'INSERT INTO `:db`.user_filter
            (name, user_id, page_name, criterias)
            VALUES (:name, :user_id, :page_name, :criterias)'
        );
        $statement = $this->db->prepare($request);
        $statement->bindValue(':name', $filter->getName(), \PDO::PARAM_STR);
        $statement->bindValue(':user_id', $filter->getUserId(), \PDO::PARAM_INT);
        $statement->bindValue(':page_name', $filter->getPageName(), \PDO::PARAM_STR);
        $statement->bindValue(':criterias', json_encode($filter->getCriterias()), \PDO::PARAM_STR);
        $statement->execute();
    }

    /**
     * @inheritDoc
     */
    public function findFiltersByUserIdWithRequestParameters(int $userId, string $pageName): array
    {
        $this->sqlRequestTranslator->setConcordanceArray([
            'id' => 'id',
            'name' => 'name',
        ]);

        // Search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();

        // Sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();

        // Pagination
        $paginationRequest = $this->sqlRequestTranslator->translatePaginationToSql();

        return $this->findFiltersByUserId($userId, $pageName, $searchRequest, $sortRequest, $paginationRequest);
    }

    /**
     * @inheritDoc
     */
    public function findFiltersByUserIdWithoutRequestParameters(int $userId, string $pageName): array
    {
        return $this->findFiltersByUserId($userId, $pageName, null, null, null);
    }

    /**
     * Retrieve all filters linked to a user id
     *
     * @param int $userId user id for which we want to find filters
     * @param string $pageName page name
     * @param string|null $searchRequest search request
     * @param string|null $sortRequest sort request
     * @param string|null $paginationRequest pagination request
     * @return Filter[]
     * @throws \Exception
     */
    private function findFiltersByUserId(
        int $userId,
        string $pageName,
        ?string $searchRequest = null,
        ?string $sortRequest = null,
        ?string $paginationRequest = null
    ): array {
        $request = $this->translateDbName('
            SELECT SQL_CALC_FOUND_ROWS id, name, user_id, page_name, criterias
            FROM `:db`.user_filter
        ');

        // Search
        $request .= !is_null($searchRequest) ? $searchRequest . ' AND ' : ' WHERE ';
        $request .= 'user_id = :user_id AND page_name = :page_name';
        $this->sqlRequestTranslator->addSearchValue(':user_id', [\PDO::PARAM_INT => $userId]);
        $this->sqlRequestTranslator->addSearchValue(':page_name', [\PDO::PARAM_STR => $pageName]);

        // Sort
        $request .= !is_null($sortRequest) ? $sortRequest : ' ORDER BY id ASC';

        // Pagination
        $request .= !is_null($paginationRequest) ? $paginationRequest : '';

        $statement = $this->db->prepare($request);

        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }

        $statement->execute();

        $result = $this->db->query('SELECT FOUND_ROWS()');
        if ($result !== false && ($total = $result->fetchColumn()) !== false) {
            $this->sqlRequestTranslator->getRequestParameters()->setTotal((int) $total);
        }

        $filters = [];
        while (false !== ($result = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $result['criterias'] = json_decode($result['criterias'], true);

            /**
             * @var Filter $filter
             */
            $filter = EntityCreator::createEntityByArray(
                Filter::class,
                $result
            );

            $filters[] = $filter;
        }

        return $filters;
    }

    /**
     * @inheritDoc
     */
    public function findFilterByUserIdAndName(int $userId, string $pageName, string $name): ?Filter
    {
        $request = $this->translateDbName('
            SELECT id, name, user_id, page_name, criterias
            FROM `:db`.user_filter
            WHERE user_id = :user_id
            AND page_name = :page_name
            AND name = :name
        ');

        $statement = $this->db->prepare($request);
        $statement->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $statement->bindValue(':page_name', $pageName, \PDO::PARAM_STR);
        $statement->bindValue(':name', $name, \PDO::PARAM_STR);
        $statement->execute();

        if (false !== ($filter = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $filter['criterias'] = json_decode($filter['criterias'], true);
            return EntityCreator::createEntityByArray(
                Filter::class,
                $filter
            );
        } else {
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function findFilterByUserIdAndId(int $userId, string $pageName, int $filterId): ?Filter
    {
        $request = $this->translateDbName('
            SELECT id, name, user_id, page_name, criterias
            FROM `:db`.user_filter
            WHERE user_id = :user_id
            AND id = :filter_id
            AND page_name = :page_name
        ');

        $statement = $this->db->prepare($request);
        $statement->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $statement->bindValue(':filter_id', $filterId, \PDO::PARAM_INT);
        $statement->bindValue(':page_name', $pageName, \PDO::PARAM_STR);
        $statement->execute();

        if (false !== ($filter = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $filter['criterias'] = json_decode($filter['criterias'], true);
            return EntityCreator::createEntityByArray(
                Filter::class,
                $filter
            );
        } else {
            return null;
        }
    }
}
