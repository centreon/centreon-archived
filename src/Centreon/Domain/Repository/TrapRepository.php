<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class TrapRepository extends ServiceEntityRepository
{
    /**
     * Export
     *
     * @param int $pollerId
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
        $sqlFilter = static::exportFilterSql($pollerIds);
        $sql = <<<SQL
SELECT
    t.*
FROM traps AS t
INNER JOIN traps_service_relation AS tsr ON tsr.traps_id = t.traps_id AND
    (tsr.service_id IN ({$sqlFilter}){$sqlFilterList})
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
     * Export filter
     *
     * @param int[] $pollerIds
     * @return string
     */
    public static function exportFilterSql(array $pollerIds) : string
    {
        $ids = join(',', $pollerIds);
        $sql = <<<SQL
SELECT
    _t.service_service_id
FROM host_service_relation AS _t
LEFT JOIN hostgroup AS _hg ON _hg.hg_id = _t.hostgroup_hg_id
LEFT JOIN hostgroup_relation AS _hgr ON _hgr.hostgroup_hg_id = _hg.hg_id
INNER JOIN ns_host_relation AS _hr ON _hr.host_host_id = _t.host_host_id OR _hr.host_host_id = _hgr.host_host_id
WHERE _hr.nagios_server_id IN ({$ids})
GROUP BY _t.service_service_id
SQL;
        return $sql;
    }
}
