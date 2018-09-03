<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use PDO;

class DowntimeHostGroupRelationRepository extends ServiceEntityRepository
{

    /**
     * Export
     * 
     * @param int $pollerId
     * @param array $hostTemplateChain
     * @return array
     */
    public function export(int $pollerId, array $hostTemplateChain = null): array
    {
        $hostList = join(',', $hostTemplateChain ?? []);
        $sqlFilterHostList = $hostList ? " OR hgr.host_host_id IN ({$hostList})" : '';

        $sql = <<<SQL
SELECT
    t.*
FROM downtime_hostgroup_relation AS t
INNER JOIN hostgroup_relation AS hgr ON hgr.hostgroup_hg_id = t.hg_hg_id
    AND hgr.host_host_id IN (SELECT t1a.host_host_id
        FROM
            ns_host_relation AS t1a
        WHERE
            t1a.nagios_server_id = :id
        GROUP BY t1a.host_host_id){$sqlFilterHostList}
GROUP BY t.dt_id, t.hg_hg_id
SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $pollerId, PDO::PARAM_INT);
        $stmt->execute();

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[] = $row;
        }

        return $result;
    }
}
