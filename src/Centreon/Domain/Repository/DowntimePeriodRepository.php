<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use PDO;

class DowntimePeriodRepository extends ServiceEntityRepository
{

    /**
     * Export
     * 
     * @param int $pollerId
     * @param array $hostTemplateChain
     * @param array $serviceTemplateChain
     * @return array
     */
    public function export(int $pollerId, array $hostTemplateChain = null, array $serviceTemplateChain = null): array
    {
        $sqlFilter = DowntimeRepository::getFilterSql($hostTemplateChain, $serviceTemplateChain);
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
        $stmt->bindParam(':id', $pollerId, PDO::PARAM_INT);
        $stmt->execute();

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[] = $row;
        }

        return $result;
    }
}
