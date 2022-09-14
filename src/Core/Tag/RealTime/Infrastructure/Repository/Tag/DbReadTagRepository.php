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

namespace Core\Tag\RealTime\Infrastructure\Repository\Tag;

use Centreon\Domain\Log\LoggerTrait;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Tag\RealTime\Application\Repository\ReadTagRepositoryInterface;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;

class DbReadTagRepository extends AbstractRepositoryDRB implements ReadTagRepositoryInterface
{
    use LoggerTrait;

    /**
     * @var SqlRequestParametersTranslator
     */
    private SqlRequestParametersTranslator $sqlRequestTranslator;

    /**
     * @param DatabaseConnection $db
     */
    public function __construct(DatabaseConnection $db, SqlRequestParametersTranslator $sqlRequestTranslator)
    {
        $this->db = $db;
        $this->sqlRequestTranslator = $sqlRequestTranslator;
        $this->sqlRequestTranslator
            ->getRequestParameters()
            ->setConcordanceStrictMode(RequestParameters::CONCORDANCE_MODE_STRICT);
        $this->sqlRequestTranslator->setConcordanceArray([
            'name' => 'tags.name'
        ]);
    }

    /**
     * @inheritDoc
     */
    public function findAllByTypeId(int $typeId): array
    {
        $this->info('Fetching tags from database of type', ['type' => $typeId]);

        $request = 'SELECT SQL_CALC_FOUND_ROWS id, name, `type`
            FROM `:dbstg`.tags';

        // Handle search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        $request .= $searchRequest === null ? ' WHERE ' : $searchRequest . ' AND ';

        $request .= ' type = :type AND EXISTS (
            SELECT 1 FROM `:dbstg`.resources_tags AS rtags
            WHERE rtags.tag_id = tags.tag_id
        )';

        // Handle sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $request .= $sortRequest !== null ? $sortRequest : ' ORDER BY name ASC';

        // Handle pagination
        $request .= $this->sqlRequestTranslator->translatePaginationToSql();

        $statement = $this->db->prepare($this->translateDbName($request));

        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            /** @var int */
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }

        $statement->bindValue(':type', $typeId, \PDO::PARAM_INT);
        $statement->execute();

        // Set total
        $result = $this->db->query('SELECT FOUND_ROWS()');
        if ($result !== false && ($total = $result->fetchColumn()) !== false) {
            $this->sqlRequestTranslator->getRequestParameters()->setTotal((int) $total);
        }

        $tags = [];
        while ($record = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $tags[] = DbTagFactory::createFromRecord($record);
        }

        return $tags;
    }

    /**
     * @inheritDoc
     */
    public function findAllByResourceAndTypeId(int $id, int $parentId, int $typeId): array
    {
        $this->info(
            'Fetching tags from database for specified resource id, parentId and typeId',
            [
                'id' => $id,
                'parentId' => $parentId,
                'type' => $typeId
            ]
        );

        $request = 'SELECT tags.id AS id, tags.name AS name, tags.`type` AS `type`
            FROM `:dbstg`.tags
            LEFT JOIN `:dbstg`.resources_tags
                ON tags.tag_id = resources_tags.tag_id
            LEFT JOIN `:dbstg`.resources
                ON resources_tags.resource_id = resources.resource_id
            WHERE resources.id = :id AND resources.parent_id = :parentId AND tags.type = :typeId';

        $statement = $this->db->prepare($this->translateDbName($request));
        $statement->bindValue(':id', $id, \PDO::PARAM_INT);
        $statement->bindValue(':parentId', $parentId, \PDO::PARAM_INT);
        $statement->bindValue(':typeId', $typeId, \PDO::PARAM_INT);
        $statement->execute();

        $tags = [];
        while ($record = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $tags[] = DbTagFactory::createFromRecord($record);
        }

        return $tags;
    }
}
