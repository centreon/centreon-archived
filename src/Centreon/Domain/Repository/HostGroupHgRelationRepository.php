<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class HostGroupHgRelationRepository extends ServiceEntityRepository
{

    /**
     * Export host's groups
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
    hghgr.*
FROM hostgroup AS t
INNER JOIN hostgroup_hg_relation AS hghgr ON hghgr.hg_child_id = t.hg_id
INNER JOIN hostgroup_relation AS hg ON hg.hostgroup_hg_id = t.hg_id
INNER JOIN ns_host_relation AS hr ON hr.host_host_id = hg.host_host_id
WHERE hr.nagios_server_id IN ({$ids})
GROUP BY hghgr.hgr_id
SQL;

        if ($templateChainList) {
            $list = join(',', $templateChainList);
            $sql .= <<<SQL

UNION

SELECT
    hghgr2.*
FROM hostgroup AS tt
INNER JOIN hostgroup_hg_relation AS hghgr2 ON hghgr2.hg_child_id = tt.hg_id
INNER JOIN hostgroup_relation AS hg2 ON hg2.hostgroup_hg_id = tt.hg_id AND hg2.host_host_id IN ({$list})
GROUP BY hghgr2.hgr_id
SQL;
        }

        $sql .= <<<SQL
) AS l
GROUP BY l.hgr_id
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
