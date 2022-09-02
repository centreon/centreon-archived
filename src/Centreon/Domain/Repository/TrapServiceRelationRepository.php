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
    public function export(array $pollerIds, array $templateChainList = null): array
    {
        // prevent SQL exception
        if (!$pollerIds) {
            return [];
        }

        $ids = join(',', $pollerIds);
        $list = join(',', $templateChainList ?? []);
        $sqlFilterList = $list ? " OR t.service_id IN ({$list})" : '';
        $sqlFilter = TrapRepository::exportFilterSql($pollerIds);
        $sql = <<<SQL
SELECT
    t.*
FROM traps_service_relation AS t
WHERE t.service_id IN ({$sqlFilter}){$sqlFilterList}
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
