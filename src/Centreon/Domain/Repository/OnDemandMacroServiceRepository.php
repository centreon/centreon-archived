<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class OnDemandMacroServiceRepository extends ServiceEntityRepository
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
FROM on_demand_macro_service AS t
WHERE t.svc_svc_id IN ({$ids})
GROUP BY t.svc_macro_id
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
