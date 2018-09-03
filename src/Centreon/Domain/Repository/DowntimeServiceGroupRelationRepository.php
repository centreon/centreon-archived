<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use PDO;

class DowntimeServiceGroupRelationRepository extends ServiceEntityRepository
{

    /**
     * Export
     * 
     * @param int $pollerId
     * @param array $serviceTemplateChain
     * @return array
     */
    public function export(int $pollerId, array $serviceTemplateChain = null): array
    {
        $serviceList = join(',', $serviceTemplateChain ?? []);
        $sqlFilterServiceList = $serviceList ? " OR dsgr.sg_sg_id IN ({$serviceList})" : '';

        $sql = <<<SQL
SELECT
    t.dt_id
FROM downtime AS t
INNER JOIN downtime_servicegroup_relation AS dsgr ON dsgr.dt_id = t.dt_id
    AND dsgr.sg_sg_id IN (SELECT t1a.host_host_id
        FROM
            ns_host_relation AS t1a
        WHERE
            t1a.nagios_server_id = :id
        GROUP BY t1a.host_host_id){$sqlFilterServiceList}
GROUP BY t.dt_id
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
