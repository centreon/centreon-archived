<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class HostCategoryRepository extends ServiceEntityRepository
{
    /**
     * Export
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
FROM hostcategories AS t
INNER JOIN hostcategories_relation AS hc ON hc.hostcategories_hc_id = t.hc_id
INNER JOIN host AS h ON h.host_id = hc.host_host_id
INNER JOIN ns_host_relation AS hr ON hr.host_host_id = h.host_id
WHERE hr.nagios_server_id IN ({$ids})
GROUP BY t.hc_id
SQL;
        if ($templateChainList) {
            $list = join(',', $templateChainList);
            $sql .= <<<SQL

UNION

SELECT
    tt.*
FROM hostcategories AS tt
INNER JOIN hostcategories_relation AS hc ON hc.hostcategories_hc_id = tt.hc_id AND hc.host_host_id IN ({$list})
GROUP BY tt.hc_id
SQL;
        }

        $sql .= <<<SQL
) AS l
GROUP BY l.hc_id
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
