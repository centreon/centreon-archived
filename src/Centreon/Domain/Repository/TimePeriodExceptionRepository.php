<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class TimePeriodExceptionRepository extends ServiceEntityRepository
{
    /**
     * Export
     *
     * @param array $timeperiodList
     * @return array
     */
    public function export(array $timeperiodList = null): array
    {
        if (!$timeperiodList) {
            return [];
        }

        $list = join(',', $timeperiodList);

        $sql = <<<SQL
SELECT
    t.*
FROM timeperiod_exceptions AS t
WHERE t.timeperiod_id IN ({$list})
GROUP BY t.exception_id
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
