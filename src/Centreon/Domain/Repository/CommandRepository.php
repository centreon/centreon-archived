<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class CommandRepository extends ServiceEntityRepository
{

    /**
     * Export
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
SELECT
    t3.command_id, t3.command_name, t3.command_line, t3.graph_id
FROM command AS t3
INNER JOIN host AS h3 ON
    h3.command_command_id = t3.command_id
INNER JOIN ns_host_relation AS nhr3 ON nhr3.host_host_id = h3.host_id
WHERE
    nhr3.nagios_server_id IN ({$ids})
GROUP BY t3.command_id

UNION

SELECT
    t.command_id, t.command_name, t.command_line, t.graph_id
FROM command AS t
INNER JOIN service AS s ON
    s.command_command_id = t.command_id
INNER JOIN host_service_relation AS hsr ON
    hsr.service_service_id = s.service_id
LEFT JOIN hostgroup_relation AS hgr ON hgr.hostgroup_hg_id = hsr.hostgroup_hg_id
LEFT JOIN ns_host_relation AS nhr ON
	nhr.host_host_id = hsr.host_host_id OR
	nhr.host_host_id = hgr.host_host_id
WHERE
    nhr.nagios_server_id IN ({$ids})
GROUP BY t.command_id
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
     * Export
     *
     * @param int[] $ids
     * @return array
     */
    public function exportList(array $list): array
    {
        // prevent SQL exception
        if (!$list) {
            return [];
        }

        $ids = join(',', $list);

        $sql = <<<SQL
SELECT
--    t.*
    t.command_id, t.command_name, t.command_line, t.graph_id
FROM command AS t
WHERE t.command_id IN ({$ids})
GROUP BY t.command_id
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
TRUNCATE TABLE `command`;
TRUNCATE TABLE `connector`;
TRUNCATE TABLE `command_arg_description`;
TRUNCATE TABLE `command_categories_relation`;
TRUNCATE TABLE `command_categories`;
TRUNCATE TABLE `on_demand_macro_command`;
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }
}
