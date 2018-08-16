<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use PDO;

class HostRepository extends ServiceEntityRepository
{

    /**
     * Export hosts
     * 
     * @param int $pollerId
     * @return array
     */
    public function export(int $pollerId): array
    {
        $sql = <<<SQL
SELECT
    t.*,
    GROUP_CONCAT(hgr.hostgroup_hg_id) AS `groups`
FROM host AS t
INNER JOIN ns_host_relation AS hr ON hr.host_host_id = t.host_id
LEFT JOIN hostgroup_relation AS hgr ON hgr.host_host_id = t.host_id
WHERE hr.nagios_server_id = :id
GROUP BY t.host_id
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
