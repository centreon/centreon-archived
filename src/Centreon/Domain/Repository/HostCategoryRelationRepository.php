<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class HostCategoryRelationRepository extends ServiceEntityRepository
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
FROM hostcategories_relation AS t
WHERE t.host_host_id IN ({$ids})
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