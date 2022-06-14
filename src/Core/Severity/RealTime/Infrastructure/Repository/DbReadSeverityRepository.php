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

namespace Core\Severity\RealTime\Infrastructure\Repository;

use Centreon\Domain\Log\LoggerTrait;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;
use Core\Severity\RealTime\Application\Repository\ReadSeverityRepositoryInterface;

class DbReadSeverityRepository extends AbstractRepositoryDRB implements ReadSeverityRepositoryInterface
{
    use LoggerTrait;

    /**
     * @var SqlRequestParametersTranslator
     */
    private SqlRequestParametersTranslator $sqlRequestTranslator;

    public function __construct(DatabaseConnection $db, SqlRequestParametersTranslator $sqlRequestTranslator)
    {
        $this->db = $db;
        $this->sqlRequestTranslator = $sqlRequestTranslator;
        $this->sqlRequestTranslator
            ->getRequestParameters()
            ->setConcordanceStrictMode(RequestParameters::CONCORDANCE_MODE_STRICT);
        $this->sqlRequestTranslator->setConcordanceArray([
            'name' => 's.name',
            'level' => 's.level'
        ]);
    }

    /**
     * @inheritDoc
     */
    public function findAllByTypeId(int $typeId): array
    {
        $this->info('Fetching severities from the database');

        $request = 'SELECT SQL_CALC_FOUND_ROWS
            severity_id,
            s.id,
            s.name,
            s.type,
            s.level,
            s.icon_id,
            img_id AS `icon_id`,
            img_name AS `icon_name`,
            img_path AS `icon_path`,
            imgd.dir_name AS `icon_directory`
        FROM `:dbstg`.severities s
        INNER JOIN `:db`.view_img img
            ON s.icon_id = img.img_id
        LEFT JOIN `:db`.view_img_dir_relation imgdr
            ON imgdr.img_img_id = img.img_id
        INNER JOIN `:db`.view_img_dir imgd
            ON imgd.dir_id = imgdr.dir_dir_parent_id';

        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        $request .= $searchRequest === null ? ' WHERE ' : $searchRequest . ' AND ';
        $request .= 's.type = :typeId AND img.img_id = s.icon_id';

        // Handle sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $request .= $sortRequest !== null ? $sortRequest : ' ORDER BY name ASC';

        // Handle pagination
        $request .= $this->sqlRequestTranslator->translatePaginationToSql();
        $statement = $this->db->prepare($this->translateDbName($request));

        foreach ($this->sqlRequestTranslator->getSearchValues() as $key => $data) {
            $type = key($data);
            $value = $data[$type];
            $statement->bindValue($key, $value, $type);
        }

        $statement->bindValue(':typeId', $typeId, \PDO::PARAM_INT);
        $statement->execute();

        // Set total
        $result = $this->db->query('SELECT FOUND_ROWS()');
        if ($result !== false && ($total = $result->fetchColumn()) !== false) {
            $this->sqlRequestTranslator->getRequestParameters()->setTotal((int) $total);
        }

        $severities = [];
        while ($record = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $severities[] = DbSeverityFactory::createFromRecord($record);
        }

        return $severities;
    }
}
