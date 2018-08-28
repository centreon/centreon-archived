<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use PDO;

class HostServiceRelationRepository extends ServiceEntityRepository
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
FROM host_service_relation AS t
LEFT JOIN hostgroup AS hg ON hg.hg_id = t.hostgroup_hg_id
LEFT JOIN hostgroup_relation AS hgr ON hgr.hostgroup_hg_id = hg.hg_id
INNER JOIN ns_host_relation AS hr ON hr.host_host_id = t.host_host_id OR hr.host_host_id = hgr.host_host_id
WHERE hr.nagios_server_id = :id
GROUP BY t.hsr_id
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
