<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use PDO;

class HostRepository extends ServiceEntityRepository
{

    /**
     * Export hosts
     *
     * @param int[] $hostList
     * @param array $templateChainList
     * @return array
     */
    public function export(array $hostList, array $templateChainList = null): array
    {
        // prevent SQL exception
        if (!$hostList) {
            return [];
        }

        $ids = join(',', $hostList);

        $sql = <<<SQL
SELECT l.* FROM (
SELECT
    t.*,
    hr.nagios_server_id AS `_nagios_id`
FROM host AS t
INNER JOIN ns_host_relation AS hr ON hr.host_host_id = t.host_id
WHERE hr.host_host_id IN ({$ids})
GROUP BY t.host_id
SQL;

        if ($templateChainList) {
            $list = join(',', $templateChainList);
            $sql .= <<<SQL

UNION

SELECT
tt.*,
NULL AS `_nagios_id`
FROM host AS tt
WHERE tt.host_id IN ({$list})
GROUP BY tt.host_id
SQL;
        }

        $sql .= <<<SQL
) AS l
GROUP BY l.host_id
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
     * Truncate tables before import of data
     * 
     * @return void
     */
    public function truncate():void
    {
        $sql = <<<SQL
TRUNCATE TABLE `ns_host_relation`;
TRUNCATE TABLE `hostgroup_relation`;
TRUNCATE TABLE `hostgroup`;
TRUNCATE TABLE `hostcategories_relation`;
TRUNCATE TABLE `hostcategories`;
TRUNCATE TABLE `host_hostparent_relation`;
TRUNCATE TABLE `on_demand_macro_host`;
TRUNCATE TABLE `hostgroup_hg_relation`;
TRUNCATE TABLE `extended_host_information`;
TRUNCATE TABLE `host`;
TRUNCATE TABLE `host_template_relation`;
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }

    /**
     * Get the list of host IDs
     *
     * @param int[] $pollerIds
     * @param int[] $ba
     * @return array
     */
    public function getHostIdsByPoller(array $pollerIds, array $ba = null): array
    {
        // prevent SQL exception
        if (!$pollerIds) {
            return [];
        }

        $ids = join(',', $pollerIds);
        $sql = <<<SQL
SELECT l.* FROM (
SELECT
    host_host_id AS `id`
FROM ns_host_relation
WHERE nagios_server_id IN ({$ids})
SQL;

        // Extract BA hosts
        if ($ba) {
            foreach ($ba as $key => $val) {
                $ba[$key] = "'ba_{$val}'";
            }

            $ba = implode(',', $ba);
            $sql .= " UNION SELECT t2.host_host_id AS `id`"
                . " FROM host_service_relation AS t2"
                . " INNER JOIN service s2 ON s2.service_id = t2.service_service_id"
                . " AND s2.service_description IN({$ba}) GROUP BY t2.host_host_id";
        }

        $sql .= ") AS l GROUP BY l.id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[] = $row['id'];
        }

        return $result;
    }
}
