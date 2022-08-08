<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class OnDemandMacroServiceRepository extends ServiceEntityRepository
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

        $ids = join(',', $pollerIds);

        $sql = <<<SQL
SELECT l.* FROM(
SELECT
    t.*
FROM on_demand_macro_service AS t
INNER JOIN host_service_relation AS hsr ON hsr.service_service_id = t.svc_svc_id
LEFT JOIN hostgroup AS hg ON hg.hg_id = hsr.hostgroup_hg_id
LEFT JOIN hostgroup_relation AS hgr ON hgr.hostgroup_hg_id = hg.hg_id
INNER JOIN ns_host_relation AS hr ON hr.host_host_id = hsr.host_host_id OR hr.host_host_id = hgr.host_host_id
WHERE hr.nagios_server_id IN ({$ids})
GROUP BY t.svc_macro_id
SQL;

        if ($templateChainList) {
            $list = join(',', $templateChainList);
            $sql .= <<<SQL

UNION

SELECT
    tt.*
FROM on_demand_macro_service AS tt
WHERE tt.svc_svc_id IN ({$list})
GROUP BY tt.svc_macro_id
SQL;
        }

        $sql .= <<<SQL
) AS l
GROUP BY l.svc_macro_id
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
