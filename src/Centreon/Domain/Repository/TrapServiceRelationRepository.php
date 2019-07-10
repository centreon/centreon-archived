<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class TrapServiceRelationRepository extends ServiceEntityRepository
{

    /**
     * Export
     *
     * @param int[] $pollerIds
     * @param array $templateChainList
     * @return array
     */
    public function export(array $serviceList, array $templateChainList = null): array
    {
        // prevent SQL exception
        if (!$serviceList) {
            return [];
        }

        if ($templateChainList) {
            $serviceList = array_merge($serviceList, $templateChainList);
        }

        $ids = implode(',', $serviceList);

        $sql = <<<SQL
SELECT
    t.*
FROM traps_service_relation AS t
WHERE t.service_id IN ({$ids})
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
