<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class DowntimePeriodRepository extends ServiceEntityRepository
{
    /**
     * Export
     *
     * @param int[] $pollerIds
     * @param array $hostTemplateChain
     * @param array $serviceTemplateChain
     * @return array
     */
    public function export(array $pollerIds, array $hostTemplateChain = null, array $serviceTemplateChain = null): array
    {
        if (!$pollerIds) {
            return [];
        }

        $sqlFilter = DowntimeRepository::getFilterSql($pollerIds, $hostTemplateChain, $serviceTemplateChain);
        $sql = <<<SQL
SELECT
    t.*
FROM downtime_period AS t
WHERE t.dt_id IN ({$sqlFilter})
GROUP BY t.dt_id
SQL;

        $sql2 = <<<SQL
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
