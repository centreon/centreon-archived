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

namespace Centreon\Infrastructure\HostConfiguration\Repository;

use Centreon\Domain\HostConfiguration\Interfaces\HostSeverityReadRepositoryInterface;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\HostConfiguration\Repository\Model\HostSeverityFactoryRdb;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\RequestParameters\Interfaces\NormalizerInterface;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;

/**
 * This class is designed to represent the MariaDb repository to manage host severity.
 *
 * @package Centreon\Infrastructure\HostConfiguration\Repository
 */
class HostSeverityRepositoryRDB extends AbstractRepositoryDRB implements HostSeverityReadRepositoryInterface
{
    /**
     * @var SqlRequestParametersTranslator
     */
    private $sqlRequestTranslator;

    public function __construct(DatabaseConnection $db, SqlRequestParametersTranslator $sqlRequestTranslator)
    {
        $this->db = $db;
        $this->sqlRequestTranslator = $sqlRequestTranslator;
        $this->sqlRequestTranslator
            ->getRequestParameters()
            ->setConcordanceStrictMode(RequestParameters::CONCORDANCE_MODE_STRICT);
    }

    /**
     * @inheritDoc
     */
    public function findHostSeverities(): array
    {
        $this->sqlRequestTranslator->setConcordanceArray(
            [
                'id' => 'hc_id',
                'name' => 'hc_name',
                'alias' => 'hc_alias',
                'level' => 'level',
                'icon' => 'icon_id',
                'is_activated' => 'hc_activate',
            ]
        );
        $this->sqlRequestTranslator->addNormalizer(
            'is_activated',
            new class implements NormalizerInterface
            {
                public function normalize($valueToNormalize)
                {
                    if (is_bool($valueToNormalize)) {
                        return ($valueToNormalize === true) ? '1' : '0';
                    }
                    return $valueToNormalize;
                }
            }
        );
        $request = $this->translateDbName(
            'SELECT SQL_CALC_FOUND_ROWS hc.*, icon.img_id AS img_id, icon.img_name AS img_name,
                CONCAT(iconD.dir_name,\'/\',icon.img_path) AS img_path, icon.img_comment AS img_comment
            FROM `:db`.hostcategories hc
            LEFT JOIN `:db`.view_img icon
                ON icon.img_id = hc.icon_id
            LEFT JOIN `centreon`.view_img_dir_relation iconR
                ON iconR.img_img_id = icon.img_id
            LEFT JOIN `centreon`.view_img_dir iconD
                ON iconD.dir_id = iconR.dir_dir_parent_id'
        );

        // Search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        $request .= !is_null($searchRequest)
            ? $searchRequest . ' AND level IS NOT NULL'
            : '  WHERE level IS NOT NULL';

        // Sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $request .= !is_null($sortRequest)
            ? $sortRequest
            : ' ORDER BY hc_name ASC';

        // Pagination
        $request .= $this->sqlRequestTranslator->translatePaginationToSql();
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
        $hostSeverities = [];
        while (($record = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $hostSeverities[] = HostSeverityFactoryRdb::create($record);
        }
        return $hostSeverities;
    }
}
