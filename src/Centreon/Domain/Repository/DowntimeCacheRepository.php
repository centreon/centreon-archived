<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class DowntimeCacheRepository extends ServiceEntityRepository
{
    /**
     * Export
     *
     * @param int[] $pollerIds
     * @param array $hostTemplateChain
     * @return array
     */
    public function export(array $pollerIds, array $hostTemplateChain = null): array
    {
        // prevent SQL exception
        if (!$pollerIds) {
            return [];
        }

        $ids = join(',', $pollerIds);
        $hostList = join(',', $hostTemplateChain ?? []);
        $sqlFilterHostList = $hostList ? " OR t.host_id IN ({$hostList})" : '';

        $sql = <<<SQL
SELECT
    t.*
FROM downtime_cache AS t
WHERE t.host_id IN (SELECT t1a.host_host_id
        FROM
            ns_host_relation AS t1a
        WHERE
            t1a.nagios_server_id IN ({$ids})
        GROUP BY t1a.host_host_id){$sqlFilterHostList}
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
