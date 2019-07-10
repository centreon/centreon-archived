<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class HostGroupRepository extends ServiceEntityRepository
{

    /**
     * Export host's groups
     *
     * @param int[] $hostList
     * @param array $templateChainList
     * @return array
     */
    public function export(array $hostList, array $templateChainList = null): array
    {
        // prevent SQL exception
        if (!$hostList) {
            return [];
        }

        if ($templateChainList) {
            $hostList = array_merge($hostList, $templateChainList);
        }

        $ids = join(',', $hostList);

        $sql = <<<SQL
SELECT
    t.*
FROM hostgroup AS t
INNER JOIN hostgroup_relation AS hg ON hg.hostgroup_hg_id = t.hg_id
WHERE hg.host_host_id IN ({$ids})
GROUP BY t.hg_id
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
