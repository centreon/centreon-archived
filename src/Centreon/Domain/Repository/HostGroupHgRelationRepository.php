<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use PDO;

class HostGroupHgRelationRepository extends ServiceEntityRepository
{

    /**
     * Export host's groups
     * 
     * @param int $pollerId
     * @return array
     */
    public function export(int $pollerId): array
    {
        // @todo is a parent has relation to the poller
        $sql = <<<SQL
SELECT
    hghgr.*
FROM hostgroup AS t
INNER JOIN hostgroup_hg_relation AS hghgr ON hghgr.hg_child_id = t.hg_id
INNER JOIN hostgroup_relation AS hg ON hg.hostgroup_hg_id = t.hg_id
INNER JOIN ns_host_relation AS hr ON hr.host_host_id = hg.host_host_id
WHERE hr.nagios_server_id = :id
GROUP BY hghgr.hgr_id
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
