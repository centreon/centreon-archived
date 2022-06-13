<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class CommandArgDescriptionRepository extends ServiceEntityRepository
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
    cad1.*
FROM command AS t1
INNER JOIN command_arg_description AS cad1 ON cad1.cmd_id = t1.command_id
INNER JOIN cfg_nagios AS cn1 ON
    cn1.global_service_event_handler = t1.command_id OR
    cn1.global_host_event_handler = t1.command_id
WHERE
    cn1.nagios_id IN ({$ids})
GROUP BY cad1.cmd_id

UNION

SELECT
    cad2.*
FROM command AS t2
INNER JOIN command_arg_description AS cad2 ON cad2.cmd_id = t2.connector_id
INNER JOIN poller_command_relations AS pcr2 ON pcr2.command_id = t2.command_id
WHERE
    pcr2.poller_id IN ({$ids})
GROUP BY cad2.cmd_id

UNION

SELECT
    cad3.*
FROM command AS t3
INNER JOIN command_arg_description AS cad3 ON cad3.cmd_id = t3.connector_id
INNER JOIN host AS h3 ON
    h3.command_command_id = t3.command_id OR
    h3.command_command_id2 = t3.command_id
INNER JOIN ns_host_relation AS nhr3 ON nhr3.host_host_id = h3.host_id
WHERE
    nhr3.nagios_server_id IN ({$ids})
GROUP BY cad3.cmd_id

UNION

SELECT
    cad4.*
FROM command AS t4
INNER JOIN command_arg_description AS cad4 ON cad4.cmd_id = t4.connector_id
INNER JOIN host AS h4 ON
    h4.command_command_id = t4.command_id OR
    h4.command_command_id2 = t4.command_id
INNER JOIN ns_host_relation AS nhr4 ON nhr4.host_host_id = h4.host_id
WHERE
    nhr4.nagios_server_id IN ({$ids})
GROUP BY cad4.cmd_id

UNION

SELECT
    cad.*
FROM command AS t
INNER JOIN command_arg_description AS cad ON cad.cmd_id = t.connector_id
INNER JOIN service AS s ON
    s.command_command_id = t.command_id OR
    s.command_command_id2 = t.command_id
INNER JOIN host_service_relation AS hsr ON
    hsr.service_service_id = s.service_id
LEFT JOIN hostgroup_relation AS hgr ON hgr.hostgroup_hg_id = hsr.hostgroup_hg_id
LEFT JOIN ns_host_relation AS nhr ON
	nhr.host_host_id = hsr.host_host_id OR
	nhr.host_host_id = hgr.host_host_id
WHERE
    nhr.nagios_server_id IN ({$ids})
GROUP BY cad.cmd_id
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
     * @param int[] $list
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
    t.*
FROM command AS t
WHERE
    t.cmd_id IN ({$ids})
GROUP BY t.cmd_id
SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[] = $row;
        }

        return $result;
    }
}
