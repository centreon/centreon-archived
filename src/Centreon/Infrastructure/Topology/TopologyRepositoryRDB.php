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

namespace Centreon\Infrastructure\Topology;

use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Domain\Topology\Interfaces\TopologyRepositoryInterface;

class TopologyRepositoryRDB implements TopologyRepositoryInterface
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
    public function disableMenus(): void
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
    public function enableMenus(): void
    {
        $this->db->query(
            "UPDATE topology SET topology_show = '1'
            WHERE topology_page IN ('21003', '601', '602', '60304', '608', '604', '617', '650', '609', '610', '50111',
            '50102', '50707', '50120')
            OR topology_parent IN ('601', '602', '608', '604', '617', '650', '609', '610')"
        );
    }
}