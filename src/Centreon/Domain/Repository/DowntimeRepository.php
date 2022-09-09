<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class DowntimeRepository extends ServiceEntityRepository
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

        $sqlFilter = static::getFilterSql($pollerIds, $hostTemplateChain, $serviceTemplateChain);
        $sql = <<<SQL
SELECT
    t.*
FROM downtime AS t
WHERE t.dt_id IN ({$sqlFilter})
GROUP BY t.dt_id
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
TRUNCATE TABLE `downtime_servicegroup_relation`;
TRUNCATE TABLE `downtime_service_relation`;
TRUNCATE TABLE `downtime_hostgroup_relation`;
TRUNCATE TABLE `downtime_host_relation`;
TRUNCATE TABLE `downtime_cache`;
TRUNCATE TABLE `downtime_period`;
TRUNCATE TABLE `downtime`;
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }

    public static function getFilterSql(
        array $pollerIds,
        array $hostTemplateChain = null,
        array $serviceTemplateChain = null
    ): string {
        // prevent SQL exception
        if (!$pollerIds) {
            return [];
        }

        $ids = join(',', $pollerIds);
        $hostList = join(',', $hostTemplateChain ?? []);
        $sqlFilterHostList = $hostList ? " OR dhr1.host_host_id IN ({$hostList})" : '';
        $sqlFilterHostList2 = $hostList ? " OR hgr2.host_host_id IN ({$hostList})" : '';
        $sqlFilterHostList3 = $hostList ? " OR dsr3.host_host_id IN ({$hostList})" : '';
        $sqlFilterHostList5 = $hostList ? " OR dc5.host_id IN ({$hostList})" : '';

        $serviceList = join(',', $serviceTemplateChain ?? []);
        $sqlFilterServiceList4 = $serviceList ? " OR dsgr4.sg_sg_id IN ({$serviceList})" : '';

        $sql = <<<SQL
SELECT l.* FROM (
SELECT
    t1.dt_id
FROM downtime AS t1
INNER JOIN downtime_host_relation AS dhr1 ON dhr1.dt_id = t1.dt_id
    AND dhr1.host_host_id IN (SELECT t1a.host_host_id
        FROM
            ns_host_relation AS t1a
        WHERE
            t1a.nagios_server_id IN ({$ids})
        GROUP BY t1a.host_host_id){$sqlFilterHostList}
GROUP BY t1.dt_id

UNION

SELECT
    t2.dt_id
FROM downtime AS t2
INNER JOIN downtime_hostgroup_relation AS dhgr2 ON dhgr2.dt_id = t2.dt_id
INNER JOIN hostgroup_relation AS hgr2 ON hgr2.hostgroup_hg_id = dhgr2.hg_hg_id
    AND hgr2.host_host_id IN (SELECT t2a.host_host_id
        FROM
            ns_host_relation AS t2a
        WHERE
            t2a.nagios_server_id IN ({$ids})
        GROUP BY t2a.host_host_id){$sqlFilterHostList2}
GROUP BY t2.dt_id

UNION

SELECT
    t3.dt_id
FROM downtime AS t3
INNER JOIN downtime_service_relation AS dsr3 ON dsr3.dt_id = t3.dt_id
    AND dsr3.host_host_id IN (SELECT t3a.host_host_id
        FROM
            ns_host_relation AS t3a
        WHERE
            t3a.nagios_server_id IN ({$ids})
        GROUP BY t3a.host_host_id){$sqlFilterHostList3}
GROUP BY t3.dt_id

UNION

SELECT
    t4.dt_id
FROM downtime AS t4
INNER JOIN downtime_servicegroup_relation AS dsgr4 ON dsgr4.dt_id = t4.dt_id
    AND dsgr4.sg_sg_id IN (SELECT t4a.host_host_id
        FROM
            ns_host_relation AS t4a
        WHERE
            t4a.nagios_server_id IN ({$ids})
        GROUP BY t4a.host_host_id){$sqlFilterServiceList4}
GROUP BY t4.dt_id

UNION

SELECT
    t5.dt_id
FROM downtime AS t5
INNER JOIN downtime_cache AS dc5 ON dc5.downtime_id = t5.dt_id
    AND dc5.host_id IN (SELECT t5a.host_host_id
        FROM
            ns_host_relation AS t5a
        WHERE
            t5a.nagios_server_id IN ({$ids})
        GROUP BY t5a.host_host_id){$sqlFilterHostList5}
GROUP BY t5.dt_id
) AS l
GROUP BY l.dt_id
SQL;

        return $sql;
    }
}
