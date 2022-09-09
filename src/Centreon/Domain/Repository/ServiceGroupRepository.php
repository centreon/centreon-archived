<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class ServiceGroupRepository extends ServiceEntityRepository
{
    /**
     * Export
     *
     * @todo restriction by poller
     *
     * @param int[] $pollerIds
     * @param array $templateChainList
     * @return array
     */
    public function export(array $pollerIds, array $templateChainList = null): array
    {
        // prevent SQL exception
        if (!$pollerIds) {
            return [];
        }

        $ids = join(',', $pollerIds);

        $sql = <<<SQL
SELECT l.* FROM(
SELECT
    t.*
FROM servicegroup AS t
INNER JOIN servicegroup_relation AS sgr ON sgr.servicegroup_sg_id = t.sg_id
LEFT JOIN hostgroup AS hg ON hg.hg_id = sgr.hostgroup_hg_id
LEFT JOIN hostgroup_relation AS hgr ON hgr.hostgroup_hg_id = hg.hg_id
INNER JOIN ns_host_relation AS hr ON hr.host_host_id = sgr.host_host_id OR hr.host_host_id = hgr.host_host_id
WHERE hr.nagios_server_id IN ({$ids})
GROUP BY t.sg_id
SQL;

        if ($templateChainList) {
            $list = join(',', $templateChainList);
            $sql .= <<<SQL

UNION

SELECT
    tt.*
FROM servicegroup AS tt
INNER JOIN servicegroup_relation AS _sgr ON _sgr.servicegroup_sg_id = tt.sg_id AND _sgr.service_service_id IN ({$list})
GROUP BY tt.sg_id
SQL;
        }

        $sql .= <<<SQL
) AS l
GROUP BY l.sg_id
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
