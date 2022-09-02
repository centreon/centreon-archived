<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class HostServiceRelationRepository extends ServiceEntityRepository
{
    /**
     * Export
     *
     * @todo restriction by poller
     *
     * @param int[] $pollerIds
     * @return array
     */
    public function export(array $pollerIds, array $ba = null): array
    {
        // prevent SQL exception
        if (!$pollerIds) {
            return [];
        }

        $ids = join(',', $pollerIds);

        $sql = <<<SQL
SELECT l.* FROM (
SELECT
    t.*
FROM host_service_relation AS t
LEFT JOIN hostgroup AS hg ON hg.hg_id = t.hostgroup_hg_id
LEFT JOIN hostgroup_relation AS hgr ON hgr.hostgroup_hg_id = hg.hg_id
INNER JOIN ns_host_relation AS hr ON hr.host_host_id = t.host_host_id OR hr.host_host_id = hgr.host_host_id
WHERE hr.nagios_server_id IN ({$ids})
GROUP BY t.hsr_id
SQL;

        // Extract BA services relations
        if ($ba) {
            foreach ($ba as $key => $val) {
                $ba[$key] = "'ba_{$val}'";
            }
              $ba = implode(',', $ba);
              $sql .= " UNION SELECT t2.*"
                  . " FROM host_service_relation AS t2"
                  . " INNER JOIN service s2 ON s2.service_id = t2.service_service_id"
                  . " AND s2.service_description IN({$ba}) GROUP BY t2.service_service_id";
        }
        $sql .= ") AS l GROUP BY l.hsr_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[] = $row;
        }

        return $result;
    }
}
