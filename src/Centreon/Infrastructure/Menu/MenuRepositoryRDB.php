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

namespace Centreon\Infrastructure\Menu;

use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;
use Centreon\Domain\Menu\Interfaces\MenuRepositoryInterface;
use Centreon\Domain\Menu\Model\Page;

class MenuRepositoryRDB extends AbstractRepositoryDRB implements MenuRepositoryInterface
{
    /**
     * PlatformTopologyRepositoryRDB constructor.
     * @param DatabaseConnection $db
     */
    public function __construct(DatabaseConnection $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function disableCentralMenus(): void
    {
        $this->db->query(
            "UPDATE topology SET topology_show = '0'
            WHERE topology_page IN ('21003', '601', '602', '60304', '608', '604', '617', '650', '609', '610', '50111',
            '50102', '50707', '50120')
            OR topology_parent IN ('601', '602', '608', '604', '617', '650', '609', '610')"
        );
    }

    /**
     * @inheritDoc
     */
    public function enableCentralMenus(): void
    {
        $this->db->query(
            "UPDATE topology SET topology_show = '1'
            WHERE topology_page IN ('21003', '601', '602', '60304', '608', '604', '617', '650', '609', '610', '50111',
            '50102', '50707', '50120')
            OR topology_parent IN ('601', '602', '608', '604', '617', '650', '609', '610')"
        );
    }

    /**
     * @inheritDoc
     */
    public function findPageByTopologyPage(string $topologyPage): Page
    {
        $statement = $this->db->prepare(
            $this->translateDbName(
                "SELECT topology_id, topology_url, is_react, topology_url_opt FROM `:db`.topology " .
                "WHERE topology_page = :topologyPage"
            )
        );
        $statement->bindValue(':topologyPage', $topologyPage, \PDO::PARAM_STR);
        $statement->execute();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        return (new Page())
            ->setPageNumber((int) $topologyPage)
            ->setUrl($result['topology_url'])
            ->setUrlOptions($result['topology_url_opt'])
            ->setId((int) $result['topology_id'])
            ->setIsReact($result['is_react'] === '1');
    }
}
