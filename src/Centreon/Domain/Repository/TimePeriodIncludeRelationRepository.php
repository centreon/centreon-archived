<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class TimePeriodIncludeRelationRepository extends ServiceEntityRepository
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
FROM timeperiod_include_relations AS t
WHERE t.timeperiod_id IN ({$list})
GROUP BY t.include_id
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
