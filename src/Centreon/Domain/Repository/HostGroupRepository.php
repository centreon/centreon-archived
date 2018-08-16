<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use PDO;

class HostGroupRepository extends ServiceEntityRepository
{

    /**
     * Export host's groups
     * 
     * @param int $pollerId
     * @return array
     */
    public function export(int $pollerId): array
    {
        $sql = <<<SQL
SELECT t.* FROM hostgroup AS t
INNER JOIN hostgroup_relation AS hg ON hg.hostgroup_hg_id = t.hg_id
INNER JOIN host AS h ON h.host_id = hg.host_host_id
INNER JOIN ns_host_relation AS hr ON hr.host_host_id = h.host_id
WHERE hr.nagios_server_id = :id
GROUP BY t.hg_id
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
