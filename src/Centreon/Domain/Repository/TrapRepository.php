<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class TrapRepository extends ServiceEntityRepository
{

    /**
     * Export
     *
     * @param int[] $serviceList
     * @param int[] $templateChainList
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
FROM traps AS t
INNER JOIN traps_service_relation AS tsr ON tsr.traps_id = t.traps_id
WHERE tsr.service_id IN ({$ids})
GROUP BY t.traps_id
SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[] = $row;
        }

        return $result;
    }

    public function truncate()
    {
        $sql = <<<SQL
TRUNCATE TABLE `traps_service_relation`;
TRUNCATE TABLE `traps_vendor`;
TRUNCATE TABLE `traps_preexec`;
TRUNCATE TABLE `traps_matching_properties`;
TRUNCATE TABLE `traps_group_relation`;
TRUNCATE TABLE `traps_group`;
TRUNCATE TABLE `traps`;
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }

    /**
     * Get list of SNMP traps IDs used by services
     *
     * @param int[] $serviceList
     * @param int[] $templateChainList
     * @return array
     */
    public function getTrapsByServicesIds(array $serviceList, array $templateChainList = null): array
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

SELECT DISTINCT traps_id AS `id`
FROM traps_service_relation
WHERE service_id IN ({$ids})
SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[] = $row['id'];
        }

        return $result;
    }
}