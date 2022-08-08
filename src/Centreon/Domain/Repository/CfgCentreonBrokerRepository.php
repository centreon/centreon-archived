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

namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use Centreon\Domain\Repository\Interfaces\CfgCentreonBrokerInterface;
use Centreon\Infrastructure\Service\Exception\NotFoundException;

/**
 * Repository to manage main centreon broker configuration
 * @todo move management of cfg_centreonbroker_info in another repository
 */
class CfgCentreonBrokerRepository extends ServiceEntityRepository implements CfgCentreonBrokerInterface
{
    /**
     * {@inheritDoc}
     * @throws NotFoundException
     */
    public function findCentralBrokerConfigId(): int
    {
        // to find central broker configuration,
        // we search a configuration with an input which is listing on port 5669
        $sql = 'SELECT cb.config_id '
            . 'FROM cfg_centreonbroker cb, cfg_centreonbroker_info cbi, nagios_server ns '
            . 'WHERE cb.ns_nagios_server = ns.id '
            . 'AND cb.config_id = cbi.config_id '
            . 'AND ns.localhost = "1" ' // central poller should be on localhost
            . 'AND cb.daemon = 1 ' // central broker should be linked to cbd daemon
            . 'AND cb.config_activate = "1" '
            . 'AND cbi.config_group = "input" '
            . 'AND cbi.config_value = "5669"';
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        if ($row = $stmt->fetch()) {
            $configId = $row['config_id'];
        } else {
            throw new NotFoundException(_('Central broker config id not found'));
        }

        return $configId;
    }

    /**
     * {@inheritDoc}
     * @throws NotFoundException
     */
    public function findBrokerConfigIdByPollerId(int $pollerId): int
    {
        // to find poller broker configuration,
        // we search a configuration with an input which is listing on port 5669
        $sql = 'SELECT cb.config_id '
            . 'FROM cfg_centreonbroker cb, cfg_centreonbroker_info cbi, nagios_server ns '
            . 'WHERE cb.ns_nagios_server = ns.id '
            . 'AND cb.config_id = cbi.config_id '
            . 'AND cb.ns_nagios_server = :poller_id '
            . 'AND cb.daemon = 1 ' // central broker should be linked to cbd daemon
            . 'AND cb.config_activate = "1" '
            . 'AND cbi.config_group = "input" '
            . 'AND cbi.config_value = "5669"';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':poller_id', $pollerId, \PDO::PARAM_INT);
        $stmt->execute();

        if ($row = $stmt->fetch()) {
            $configId = $row['config_id'];
        } else {
            throw new NotFoundException(_('Poller broker config id not found'));
        }

        return $configId;
    }

    /**
     * Export poller's broker configurations
     *
     * @param int[] $pollerIds
     * @return array
     */
    public function export(array $pollerIds): array
    {
        // prevent SQL exception
        if (!$pollerIds) {
            return [];
        }

        $ids = join(',', $pollerIds);

        $sql = <<<SQL
SELECT * FROM cfg_centreonbroker WHERE ns_nagios_server IN ({$ids})
SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[] = $row;
        }

        return $result;
    }

    /**
     * Truncate centreon broker configuration in database (cfg_centreonbroker, cfg_centreonbroker_info)
     */
    public function truncate(): void
    {
        $sql = <<<SQL
TRUNCATE TABLE `cfg_centreonbroker`;
TRUNCATE TABLE `cfg_centreonbroker_info`
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }
}
