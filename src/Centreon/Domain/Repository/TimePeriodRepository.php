<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use PDO;

class TimePeriodRepository extends ServiceEntityRepository
{
    /**
     * Export
     *
     * @param int[] $timeperiodList
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
FROM timeperiod AS t
WHERE t.tp_id IN ({$list})
GROUP BY t.tp_id
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
TRUNCATE TABLE `timeperiod_exclude_relations`;
TRUNCATE TABLE `timeperiod_include_relations`;
TRUNCATE TABLE `timeperiod_exceptions`;
TRUNCATE TABLE `timeperiod`;
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }

    /**
     * Get a chain of the related objects
     *
     * @param int[] $pollerIds
     * @param int[] $hostTemplateChain
     * @param int[] $serviceTemplateChain
     * @return array
     */
    public function getChainByPoller(
        array $pollerIds,
        array $hostTemplateChain = null,
        array $serviceTemplateChain = null
    ): array {
        // prevent SQL exception
        if (!$pollerIds) {
            return [];
        }

        $ids = join(',', $pollerIds);
        $hostList = join(',', $hostTemplateChain ?? []);
        $sqlFilterHostList = $hostList ? " OR h.host_id IN ({$hostList})" : '';
        $sqlFilterHostList2 = $hostList ? " OR msr2.host_id IN ({$hostList})" : '';

        $serviceList = join(',', $serviceTemplateChain ?? []);
        $sqlFilterServiceList = $serviceList ? " OR s3.service_id IN ({$serviceList})" : '';

        $sql = <<<SQL
SELECT
    t.tp_id
FROM timeperiod AS t
INNER JOIN host AS h ON h.timeperiod_tp_id = t.tp_id OR h.timeperiod_tp_id2 = t.tp_id
LEFT JOIN ns_host_relation AS hr ON hr.host_host_id = h.host_id
WHERE hr.nagios_server_id IN ({$ids}){$sqlFilterHostList} 
GROUP BY t.tp_id

UNION

SELECT
    t2.tp_id
FROM timeperiod AS t2
INNER JOIN meta_service AS ms2 ON ms2.check_period = t2.tp_id OR ms2.notification_period = t2.tp_id
INNER JOIN meta_service_relation AS msr2 ON msr2.meta_id = ms2.meta_id
LEFT JOIN ns_host_relation AS hr2 ON hr2.host_host_id = msr2.host_id
WHERE hr2.nagios_server_id IN ({$ids}){$sqlFilterHostList2} 
GROUP BY t2.tp_id

UNION

SELECT
    t3.tp_id
FROM timeperiod AS t3
INNER JOIN service AS s3 ON s3.timeperiod_tp_id = t3.tp_id OR s3.timeperiod_tp_id2 = t3.tp_id
WHERE s3.service_id IN (SELECT t3a.service_service_id
    FROM
        host_service_relation AS t3a
            LEFT JOIN
        hostgroup AS hg3a ON hg3a.hg_id = t3a.hostgroup_hg_id
            LEFT JOIN
        hostgroup_relation AS hgr3a ON hgr3a.hostgroup_hg_id = hg3a.hg_id
            INNER JOIN
        ns_host_relation AS hr3a ON hr3a.host_host_id = t3a.host_host_id
            OR hr3a.host_host_id = hgr3a.host_host_id
    WHERE
        hr3a.nagios_server_id IN ({$ids})
    GROUP BY t3a.service_service_id){$sqlFilterServiceList}
GROUP BY t3.tp_id
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[$row['tp_id']] = $row['tp_id'];
            $this->getChainByParant($row['tp_id'], $result);
        }

        return $result;
    }

    public function getChainByParant($id, &$result)
    {
        $sql = <<<SQL
SELECT
    t.timeperiod_include_id AS `id`
FROM timeperiod_include_relations  AS t
WHERE t.timeperiod_include_id IS NOT NULL AND t.timeperiod_id = :id
GROUP BY t.timeperiod_include_id

UNION

SELECT
    t.timeperiod_exclude_id AS `id`
FROM timeperiod_exclude_relations  AS t
WHERE t.timeperiod_exclude_id IS NOT NULL AND t.timeperiod_id = :id
GROUP BY t.timeperiod_exclude_id
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        while ($row = $stmt->fetch()) {
            $isExisting = array_key_exists($row['id'], $result);
            $result[$row['id']] = $row['id'];

            if (!$isExisting) {
                $this->getChainByParant($row['id'], $result);
            }
        }

        return $result;
    }

    /**
     * Get an array of all time periods IDs that are related as templates
     *
     * @param int $id ID of time period
     * @param array $result This parameter is used forward to data from the recursive method
     * @return array
     */
    public function getIncludeChainByParent($id, &$result)
    {
        $sql = 'SELECT t.timeperiod_include_id AS `id` '
            . 'FROM timeperiod_include_relations  AS t '
            . 'WHERE t.timeperiod_include_id IS NOT NULL AND t.timeperiod_id = :id '
            . 'GROUP BY t.timeperiod_include_id';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        while ($row = $stmt->fetch()) {
            $isExisting = array_key_exists($row['id'], $result);
            $result[$row['id']] = $row['id'];

            if (!$isExisting) {
                $this->getIncludeChainByParent($row['id'], $result);
            }
        }

        return $result;
    }
}
