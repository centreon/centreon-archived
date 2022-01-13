<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class DowntimeServiceGroupRelationRepository extends ServiceEntityRepository
{
    /**
     * Export
     *
     * @param int[] $pollerIds
     * @param array $serviceTemplateChain
     * @return array
     */
    public function export(array $pollerIds, array $serviceTemplateChain = null): array
    {
        // prevent SQL exception
        if (!$pollerIds) {
            return [];
        }

        $ids = join(',', $pollerIds);
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
            t1a.nagios_server_id IN ({$ids})
        GROUP BY t1a.host_host_id){$sqlFilterServiceList}
GROUP BY t.dt_id
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
