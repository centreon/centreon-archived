<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use PDO;

class TrapMatchingPropsRepository extends ServiceEntityRepository
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
SELECT l.* FROM (
SELECT
    t.*
FROM traps_matching_properties AS t
INNER JOIN traps_service_relation AS tsr ON
    tsr.traps_id = t.trap_id AND
    (tsr.service_id IN ({$sqlFilter}){$sqlFilterList})
GROUP BY t.tmo_id
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
