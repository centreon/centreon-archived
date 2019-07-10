<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class TrapVendorRepository extends ServiceEntityRepository
{

    /**
     * Export
     *
     * @param int[] $serviceList
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
FROM traps_vendor AS t
INNER JOIN traps AS tr ON tr.manufacturer_id = t.id
INNER JOIN traps_service_relation AS tsr ON tsr.traps_id = tr.traps_id
WHERE tsr.service_id IN ({$ids})
GROUP BY t.id
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