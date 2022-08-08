<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class GivGraphTemplateRepository extends ServiceEntityRepository
{
    /**
     * Export
     *
     * @param int[] $pollerIds
     * @param array $hostTemplateChain
     * @param array $serviceTemplateChain
     * @return array
     */
    public function export(array $pollerIds, array $hostTemplateChain = null, array $serviceTemplateChain = null): array
    {
        // prevent SQL exception
        if (!$pollerIds) {
            return [];
        }

        $ids = join(',', $pollerIds);
        $hostList = join(',', $hostTemplateChain ?? []);
        $sqlFilterHostList = $hostList ? " OR msr.host_id IN ({$hostList})" : '';

        $serviceList = join(',', $serviceTemplateChain ?? []);
        $sqlFilterServiceList = $serviceList ? " OR esi2.service_service_id IN ({$serviceList})" : '';

        $sql = <<<SQL
SELECT l.* FROM (
SELECT
    t.*
FROM giv_graphs_template AS t
INNER JOIN meta_service AS ms ON ms.graph_id = t.graph_id
INNER JOIN meta_service_relation AS msr ON msr.meta_id = ms.meta_id
WHERE msr.host_id IN (SELECT t1a.host_host_id
    FROM
        ns_host_relation AS t1a
    WHERE
        t1a.nagios_server_id IN ({$ids})
    GROUP BY t1a.host_host_id){$sqlFilterHostList}
GROUP BY t.graph_id

UNION

SELECT
    t2.*
FROM giv_graphs_template AS t2
INNER JOIN extended_service_information AS esi2 ON esi2.graph_id = t2.graph_id
WHERE esi2.service_service_id IN (SELECT t2a.service_service_id
    FROM
        host_service_relation AS t2a
            LEFT JOIN
        hostgroup AS hg2a ON hg2a.hg_id = t2a.hostgroup_hg_id
            LEFT JOIN
        hostgroup_relation AS hgr2a ON hgr2a.hostgroup_hg_id = hg2a.hg_id
            INNER JOIN
        ns_host_relation AS hr2a ON hr2a.host_host_id = t2a.host_host_id
            OR hr2a.host_host_id = hgr2a.host_host_id
    WHERE
        hr2a.nagios_server_id IN ({$ids})
    GROUP BY t2a.service_service_id){$sqlFilterServiceList}
GROUP BY t2.graph_id
) AS l
GROUP BY l.graph_id
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
     * Export list
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
FROM giv_graphs_template AS t
WHERE t.graph_id IN ({$ids})
GROUP BY t.graph_id
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
TRUNCATE TABLE `giv_graphs_template`;
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }
}
