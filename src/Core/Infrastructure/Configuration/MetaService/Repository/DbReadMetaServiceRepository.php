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

namespace Core\Infrastructure\Configuration\MetaService\Repository;

use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Core\Application\Configuration\MetaService\Repository\ReadMetaServiceRepositoryInterface;
use Core\Domain\Configuration\Model\MetaService;

class DbReadMetaServiceRepository extends AbstractRepositoryDRB implements ReadMetaServiceRepositoryInterface
{
    /**
     * @param DatabaseConnection $db
     */
    public function __construct(DatabaseConnection $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function findMetaServiceByIdAndAccessGroupIds(int $metaId, array $accessGroupIds): ?MetaService
    {
        if (empty($accessGroupIds)) {
            return null;
        }

        $accessGroupRequest = ' INNER JOIN `:db`.`acl_resources_meta_relations` AS armr
                ON armr.meta_id = ms.meta_id
            INNER JOIN `:db`.acl_resources res
                ON armr.acl_res_id = res.acl_res_id
            INNER JOIN `:db`.acl_res_group_relations argr
                ON res.acl_res_id = argr.acl_res_id
                AND argr.acl_group_id IN (' . implode(',', $accessGroupIds) . ') ';

        return $this->findMetaService($metaId, $accessGroupRequest);
    }

    /**
     * @inheritDoc
     */
    public function findMetaServiceById(int $metaId): ?MetaService
    {
        return $this->findMetaService($metaId);
    }

    /**
     * @param int $metaId
     * @param string|null $accessGroupRequest
     * @return MetaService|null
     */
    private function findMetaService(int $metaId, ?string $accessGroupRequest = null): ?MetaService
    {
        $request = "SELECT ms.meta_id AS `id`,
            ms.meta_name AS `name`,
            ms.meta_display AS `output`,
            ms.data_source_type,
            CASE
                WHEN ms.data_source_type = 0 THEN 'gauge'
                WHEN ms.data_source_type = 1 THEN 'counter'
                WHEN ms.data_source_type = 2 THEN 'derive'
                WHEN ms.data_source_type = 3 THEN 'absolute'
            END AS `data_source_type`,
            ms.meta_select_mode AS `meta_slection_mode`,
            ms.regexp_str,
            ms.metric,
            ms.warning,
            ms.critical,
            ms.meta_activate AS `is_activated`,
            ms.calcul_type,
            CASE
                WHEN ms.calcul_type = 'AVE' THEN 'average'
                WHEN ms.calcul_type = 'SOM' THEN 'sum'
                WHEN ms.calcul_type = 'MIN' THEN 'minimum'
                WHEN ms.calcul_type = 'MAX' THEN 'maximum'
            END AS `calculation_type`
        FROM `:db`.meta_service ms";

        $request .= ' WHERE ms.meta_id = :meta_id';

        $statement = $this->db->prepare($this->translateDbName($request));
        $statement->bindValue(':meta_id', $metaId, \PDO::PARAM_INT);

        $statement->execute();

        if ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            return DbMetaServiceFactory::createFromRecord($row);
        }

        return null;
    }
}
