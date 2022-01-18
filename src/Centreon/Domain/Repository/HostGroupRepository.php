<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class HostGroupRepository extends ServiceEntityRepository
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
    t.*
FROM hostgroup AS t
INNER JOIN hostgroup_relation AS hg ON hg.hostgroup_hg_id = t.hg_id
INNER JOIN ns_host_relation AS hr ON hr.host_host_id = hg.host_host_id
WHERE hr.nagios_server_id IN ({$ids})
GROUP BY t.hg_id
SQL;

        if ($templateChainList) {
            $list = join(',', $templateChainList);
            $sql .= <<<SQL

UNION

SELECT
    tt.*
FROM hostgroup AS tt
INNER JOIN hostgroup_relation AS hg ON hg.hostgroup_hg_id = tt.hg_id AND hg.host_host_id IN ({$list})
GROUP BY tt.hg_id
SQL;
        }

        $sql .= <<<SQL
) AS l
GROUP BY l.hg_id
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
