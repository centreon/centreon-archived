<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 *
 */

namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use PDO;
use Centreon\Infrastructure\CentreonLegacyDB\StatementCollector;

class ServiceRepository extends ServiceEntityRepository
{
    /**
     * Export
     *
     * @todo restriction by poller
     *
     * @param int[] $pollerIds
     * @param array $templateChainList
     * @return array
     */
    public function export(array $pollerIds, array $templateChainList = null): array
    {
        // prevent SQL exception
        if (!$pollerIds) {
            return [];
        }

        $ids = implode(',', $pollerIds);

        $sql = <<<SQL
SELECT l.* FROM(
SELECT
    t.*
FROM service AS t
INNER JOIN host_service_relation AS hsr ON hsr.service_service_id = t.service_id
LEFT JOIN hostgroup AS hg ON hg.hg_id = hsr.hostgroup_hg_id
LEFT JOIN hostgroup_relation AS hgr ON hgr.hostgroup_hg_id = hg.hg_id
INNER JOIN ns_host_relation AS hr ON hr.host_host_id = hsr.host_host_id OR hr.host_host_id = hgr.host_host_id
WHERE hr.nagios_server_id IN ({$ids})
GROUP BY t.service_id
SQL;

        if ($templateChainList) {
            $list = implode(',', $templateChainList);
            $sql .= <<<SQL

UNION
                
SELECT
    tt.*
FROM service AS tt
WHERE tt.service_id IN ({$list})
GROUP BY tt.service_id
SQL;
        }

        $sql .= <<<SQL
) AS l
GROUP BY l.service_id
SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[] = $row;
        }

        return $result;
    }

    public function truncate()
    {
        $sql = <<<SQL
TRUNCATE TABLE `host_service_relation`;
TRUNCATE TABLE `servicegroup_relation`;
TRUNCATE TABLE `servicegroup`;
TRUNCATE TABLE `service_categories`;
TRUNCATE TABLE `service_categories_relation`;
TRUNCATE TABLE `on_demand_macro_service`;
TRUNCATE TABLE `extended_service_information`;
TRUNCATE TABLE `service`;
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }

    /**
     * Get a chain of the related objects
     *
     * @param int[] $pollerIds
     * @param int[] $ba
     * @return array
     */
    public function getChainByPoller(array $pollerIds, array $ba = null): array
    {
        // prevent SQL exception
        if (!$pollerIds) {
            return [];
        }

        $ids = implode(',', $pollerIds);
        $sql = <<<SQL
SELECT l.* FROM (
SELECT
    t.service_template_model_stm_id AS `id`
FROM service AS t
INNER JOIN host_service_relation AS hsr ON hsr.service_service_id = t.service_id
LEFT JOIN hostgroup AS hg ON hg.hg_id = hsr.hostgroup_hg_id
LEFT JOIN hostgroup_relation AS hgr ON hgr.hostgroup_hg_id = hg.hg_id
INNER JOIN ns_host_relation AS hr ON hr.host_host_id = hsr.host_host_id OR hr.host_host_id = hgr.host_host_id
WHERE t.service_template_model_stm_id IS NOT NULL AND hr.nagios_server_id IN ({$ids})
GROUP BY t.service_template_model_stm_id
SQL;

        // Extract BA services
        if ($ba) {
            foreach ($ba as $key => $val) {
                $ba[$key] = "'ba_{$val}'";
            }

            $ba = implode(',', $ba);
            $sql .= " UNION SELECT t2.service_id AS `id` FROM service AS t2 WHERE t2.service_description IN({$ba})";
        }
        
        $sql .= ") AS l GROUP BY l.id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[$row['id']] = $row['id'];
            $this->getChainByParant($row['id'], $result);
        }

        return $result;
    }

    public function getChainByParant($id, &$result)
    {
        $sql = <<<SQL
SELECT
    t.service_template_model_stm_id AS `id`
FROM service AS t
WHERE t.service_template_model_stm_id IS NOT NULL AND t.service_id = :id
GROUP BY t.service_template_model_stm_id
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        while ($row = $stmt->fetch()) {
            $isExisting = array_key_exists($row['id'], $result);
            $result[$row['id']] = $row['id'];
            
            if (!$isExisting) {
                $this->getChainByParant($row['id'], $result);
            }
        }

        return $result;
    }

    /**
     * Remove service entity by ID
     *
     * @param int $id
     * @return void
     */
    public function removeById(int $id): void
    {
        $sql = "DELETE FROM `service`"
            . " WHERE `service_id` = :id";

        $collector = new StatementCollector;
        $collector->addValue(':id', $id);

        $stmt = $this->db->prepare($sql);
        $collector->bind($stmt);
        $stmt->execute();
    }

    /**
     * Remove relation between Service and Host
     *
     * @param int $id
     * @return void
     */
    public function removeHostRelationByServiceId(int $id): void
    {
        $sql = "DELETE FROM `host_service_relation`"
            . " WHERE `service_service_id` = :id";

        $collector = new StatementCollector;
        $collector->addValue(':id', $id);

        $stmt = $this->db->prepare($sql);
        $collector->bind($stmt);
        $stmt->execute();
    }
}
