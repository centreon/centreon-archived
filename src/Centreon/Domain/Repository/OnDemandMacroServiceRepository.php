<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use PDO;

class OnDemandMacroServiceRepository extends ServiceEntityRepository
{

    /**
     * Export
     * 
     * @todo restriction by poller
     * 
     * @param int $pollerId
     * @return array
     */
    public function export(int $pollerId): array
    {
        $sql = <<<SQL
SELECT
    t.*
FROM on_demand_macro_service AS t
INNER JOIN host_service_relation AS hsr ON hsr.service_service_id = t.svc_svc_id
LEFT JOIN hostgroup AS hg ON hg.hg_id = hsr.hostgroup_hg_id
LEFT JOIN hostgroup_relation AS hgr ON hgr.hostgroup_hg_id = hg.hg_id
INNER JOIN ns_host_relation AS hr ON hr.host_host_id = hsr.host_host_id OR hr.host_host_id = hgr.host_host_id
WHERE hr.nagios_server_id = :id
GROUP BY t.svc_macro_id
SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $pollerId, PDO::PARAM_INT);
        $stmt->execute();

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[] = $row;
        }

        return $result;
    }
}
