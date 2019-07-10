<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class HostCategoryRepository extends ServiceEntityRepository
{

    /**
     * Export
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
FROM hostcategories AS t
INNER JOIN hostcategories_relation AS hc ON hc.hostcategories_hc_id = t.hc_id
WHERE hc.host_host_id IN ({$ids})
GROUP BY t.hc_id
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
