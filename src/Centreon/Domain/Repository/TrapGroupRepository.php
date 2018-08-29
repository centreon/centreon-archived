<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use PDO;

class TrapGroupRepository extends ServiceEntityRepository
{

    /**
     * Export
     * 
     * @param int $pollerId
     * @param array $templateChainList
     * @return array
     */
    public function export(int $pollerId, array $templateChainList = null): array
    {
        $list = join(',', $templateChainList ?? []);
        $sqlFilterList = $list ? " OR tsr.service_id IN ({$list})" : '';
        $sqlFilter = TrapRepository::exportFilterSql();
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
        $stmt->bindParam(':id', $pollerId, PDO::PARAM_INT);
        $stmt->execute();

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[] = $row;
        }

        return $result;
    }
}
