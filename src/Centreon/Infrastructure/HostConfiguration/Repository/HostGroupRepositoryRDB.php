<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

use Centreon\Domain\HostConfiguration\Interfaces\HostGroup\HostGroupReadRepositoryInterface;
use Centreon\Domain\RequestParameters\RequestParameters;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\HostConfiguration\Repository\Model\HostGroupFactoryRdb;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Infrastructure\RequestParameters\Interfaces\NormalizerInterface;
use Centreon\Infrastructure\RequestParameters\SqlRequestParametersTranslator;

/**
 * This class is designed to represent the MariaDb repository to manage host groups
 *
 * @package Centreon\Infrastructure\HostConfiguration\Repository
 */
class HostGroupRepositoryRDB extends AbstractRepositoryDRB implements HostGroupReadRepositoryInterface
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
     * @throws \Assert\AssertionFailedException
     */
    public function findHostGroups(): array
    {
        $this->sqlRequestTranslator->setConcordanceArray([
            'id' => 'hg_id',
            'name' => 'hg_name',
            'alias' => 'hg_alias',
            'is_activated' => 'hg_activate',
        ]);

        $this->sqlRequestTranslator->addNormalizer(
            'is_activated',
            new class () implements NormalizerInterface
            {
                /**
                 * @inheritDoc
                 */
                public function normalize($valueToNormalize)
                {
                    if (is_bool($valueToNormalize)) {
                        return $valueToNormalize === true ? '1' : '0';
                    }
                    return $valueToNormalize;
                }
            }
        );

        $request = $this->translateDbName(
            'SELECT SQL_CALC_FOUND_ROWS hg.*, icon.img_id AS icon_id, icon.img_name AS icon_name,
                CONCAT(iconD.dir_name,\'/\',icon.img_path) AS icon_path,
                icon.img_comment AS icon_comment, imap.img_id AS imap_id, imap.img_name AS imap_name,
                CONCAT(imapD.dir_name,\'/\',imap.img_path) AS imap_path, imap.img_comment AS imap_comment
            FROM `:db`.hostgroup hg
            LEFT JOIN `:db`.view_img icon
                ON icon.img_id = hg.hg_icon_image
            LEFT JOIN `:db`.view_img_dir_relation iconR
                ON iconR.img_img_id = icon.img_id
            LEFT JOIN `:db`.view_img_dir iconD
                ON iconD.dir_id = iconR.dir_dir_parent_id
            LEFT JOIN `:db`.view_img imap
                ON imap.img_id = hg.hg_map_icon_image
            LEFT JOIN `:db`.view_img_dir_relation imapR
                ON imapR.img_img_id = imap.img_id
            LEFT JOIN `:db`.view_img_dir imapD
                ON imapD.dir_id = imapR.dir_dir_parent_id'
        );

        // Search
        $searchRequest = $this->sqlRequestTranslator->translateSearchParameterToSql();
        $request .= !is_null($searchRequest) ? $searchRequest : '';

        // Sort
        $sortRequest = $this->sqlRequestTranslator->translateSortParameterToSql();
        $request .= !is_null($sortRequest)
            ? $sortRequest
            : ' ORDER BY hg.hg_id ASC';

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
        $hostGroups = [];
        if ($statement !== false) {
            while (($result = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
                $hostGroups[] = HostGroupFactoryRdb::create($result);
            }
        }
        return $hostGroups;
    }
}
