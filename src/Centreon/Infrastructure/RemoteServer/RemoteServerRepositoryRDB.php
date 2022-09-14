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

namespace Centreon\Infrastructure\RemoteServer;

use Centreon\Domain\MonitoringServer\MonitoringServer;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Domain\RemoteServer\Interfaces\RemoteServerRepositoryInterface;
use Centreon\Infrastructure\Repository\AbstractRepositoryDRB;

class RemoteServerRepositoryRDB extends AbstractRepositoryDRB implements RemoteServerRepositoryInterface
{
    /**
     * RemoteServerRepositoryRDB constructor.
     * @param DatabaseConnection $db
     */
    public function __construct(DatabaseConnection $db)
    {
        $this->db = $db;
    }

    /**
     * @inheritDoc
     */
    public function deleteRemoteServerByServerId(int $serverId): void
    {
        $statement = $this->db->prepare(
            $this->translateDbName("DELETE FROM remote_servers WHERE server_id = :server_id")
        );
        $statement->bindValue(':server_id', $serverId, \PDO::PARAM_INT);
        $statement->execute();
    }

    /**
     * @inheritDoc
     */
    public function deleteAdditionalRemoteServer(int $monitoringServerId): void
    {
        $statement = $this->db->prepare(
            $this->translateDbName("DELETE FROM rs_poller_relation WHERE remote_server_id = :id")
        );
        $statement->bindValue(':id', $monitoringServerId, \PDO::PARAM_INT);
        $statement->execute();
    }
}
