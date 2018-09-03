<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use PDO;

class DowntimeServiceRelationRepository extends ServiceEntityRepository
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
        $sqlFilterHostList = $hostList ? " OR t.host_host_id IN ({$hostList})" : '';

        $sql = <<<SQL
SELECT
    t.*
FROM downtime_service_relation AS t
WHERE t.host_host_id IN (SELECT t1a.host_host_id
        FROM
            ns_host_relation AS t1a
        WHERE
            t1a.nagios_server_id = :id
        GROUP BY t1a.host_host_id){$sqlFilterHostList}
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
