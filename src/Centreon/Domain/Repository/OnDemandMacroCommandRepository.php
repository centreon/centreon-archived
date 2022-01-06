<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class OnDemandMacroCommandRepository extends ServiceEntityRepository
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
    odmc1.*
FROM command AS t1
INNER JOIN on_demand_macro_command AS odmc1 ON odmc1.command_command_id = t1.command_id
INNER JOIN cfg_nagios AS cn1 ON
    cn1.global_service_event_handler = t1.command_id OR
    cn1.global_host_event_handler = t1.command_id OR
    cn1.ocsp_command = t1.command_id OR
    cn1.ochp_command = t1.command_id OR
    cn1.host_perfdata_command = t1.command_id OR
    cn1.service_perfdata_command = t1.command_id OR
    cn1.host_perfdata_file_processing_command = t1.command_id OR
    cn1.service_perfdata_file_processing_command = t1.command_id
WHERE
    cn1.nagios_id IN ({$ids})
GROUP BY odmc1.command_command_id

UNION

SELECT
    odmc2.*
FROM command AS t2
INNER JOIN on_demand_macro_command AS odmc2 ON odmc2.command_command_id = t2.connector_id
INNER JOIN poller_command_relations AS pcr2 ON pcr2.command_id = t2.command_id
WHERE
    pcr2.poller_id IN ({$ids})
GROUP BY odmc2.command_command_id

UNION

SELECT
    odmc3.*
FROM command AS t3
INNER JOIN on_demand_macro_command AS odmc3 ON odmc3.command_command_id = t3.connector_id
INNER JOIN host AS h3 ON
    h3.command_command_id = t3.command_id OR
    h3.command_command_id2 = t3.command_id
INNER JOIN ns_host_relation AS nhr3 ON nhr3.host_host_id = h3.host_id
WHERE
    nhr3.nagios_server_id IN ({$ids})
GROUP BY odmc3.command_command_id

UNION

SELECT
    odmc4.*
FROM command AS t4
INNER JOIN on_demand_macro_command AS odmc4 ON odmc4.command_command_id = t4.connector_id
INNER JOIN host AS h4 ON
    h4.command_command_id = t4.command_id OR
    h4.command_command_id2 = t4.command_id
INNER JOIN ns_host_relation AS nhr4 ON nhr4.host_host_id = h4.host_id
WHERE
    nhr4.nagios_server_id IN ({$ids})
GROUP BY odmc4.command_command_id

UNION

SELECT
    odmc.*
FROM command AS t
INNER JOIN on_demand_macro_command AS odmc ON odmc.command_command_id = t.connector_id
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
GROUP BY odmc.command_command_id
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
FROM on_demand_macro_command AS t
WHERE t.command_command_id IN ({$ids})
GROUP BY t.command_macro_id
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
