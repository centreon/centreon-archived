<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class TrapGroupRepository extends ServiceEntityRepository
{
    /**
     * Export
     *
     * @param int[] $pollerIds
     * @param array $templateChainList
     * @return array
     */
    public function export(array $pollerIds, array $templateChainList = null): array
    {
        // prevent SQL exception
        if (!$pollerIds) {
            return [];
        }

        $ids = join(',', $pollerIds);
        $list = join(',', $templateChainList ?? []);
        $sqlFilterList = $list ? " OR tsr.service_id IN ({$list})" : '';
        $sqlFilter = TrapRepository::exportFilterSql($pollerIds);
        $sql = <<<SQL
SELECT
    t.*
FROM traps_group AS t
INNER JOIN traps_group_relation AS tgr ON tgr.traps_group_id = t.traps_group_id
INNER JOIN traps_service_relation AS tsr ON
    tsr.traps_id = tgr.traps_id AND
    (tsr.service_id IN ({$sqlFilter}){$sqlFilterList})
GROUP BY t.traps_group_id
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
