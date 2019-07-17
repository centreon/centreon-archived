<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use PDO;

class ServiceRepository extends ServiceEntityRepository
{

    /**
     * Export
     *
     * @param int[] $serviceList
     * @param int[] $templateChainList
     * @return array
     */
    public function export(array $serviceList, array $templateChainList = null): array
    {
        // prevent SQL exception
        if (!$serviceList) {
            return [];
        }

        if ($templateChainList) {
            $serviceList = array_merge($serviceList, $templateChainList);
        }

        $ids = implode(',', $serviceList);

        $sql = <<<SQL
SELECT t.*
FROM service AS t
WHERE t.service_id IN ({$ids})
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
TRUNCATE TABLE `host_service_relation`;
TRUNCATE TABLE `servicegroup_relation`;
TRUNCATE TABLE `servicegroup`;
TRUNCATE TABLE `service_categories`;
TRUNCATE TABLE `service_categories_relation`;
TRUNCATE TABLE `on_demand_macro_service`;
TRUNCATE TABLE `extended_service_information`;
TRUNCATE TABLE `service`;
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }

    /**
     * Get a chain of the related objects
     *
     * @param int[] $pollerIds
     * @param int[] $ba
     * @return array
     */
    public function getChainByPoller(array $pollerIds, array $ba = null): array
    {
        // prevent SQL exception
        if (!$pollerIds) {
            return [];
        }

        $ids = implode(',', $pollerIds);
        $sql = <<<SQL
SELECT l.* FROM (
SELECT
    t.service_template_model_stm_id AS `id`
FROM service AS t
INNER JOIN host_service_relation AS hsr ON hsr.service_service_id = t.service_id
LEFT JOIN hostgroup AS hg ON hg.hg_id = hsr.hostgroup_hg_id
LEFT JOIN hostgroup_relation AS hgr ON hgr.hostgroup_hg_id = hg.hg_id
INNER JOIN ns_host_relation AS hr ON hr.host_host_id = hsr.host_host_id OR hr.host_host_id = hgr.host_host_id
WHERE t.service_template_model_stm_id IS NOT NULL AND hr.nagios_server_id IN ({$ids})
GROUP BY t.service_template_model_stm_id
SQL;

        // Extract BA services
        if ($ba) {
            foreach ($ba as $key => $val) {
                $ba[$key] = "'ba_{$val}'";
            }

            $ba = implode(',', $ba);
            $sql .= " UNION SELECT t2.service_id AS `id` FROM service AS t2 WHERE t2.service_description IN({$ba})";
        }
        
        $sql .= ") AS l GROUP BY l.id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = [];

        while ($row = $stmt->fetch()) {
            if ($row['id']) {
                $result[$row['id']] = $row['id'];
                $this->getChainByParant($row['id'], $result);
            }
        }

        return $result;
    }

    /**
     * Get a chain of the related object by parant
     *
     * @param int $id
     * @param int[] &$result
     * @return array
     */
    public function getChainByParant($id, &$result)
    {
        $sql = <<<SQL
SELECT
    t.service_template_model_stm_id AS `id`
FROM service AS t
WHERE t.service_template_model_stm_id IS NOT NULL AND t.service_id = :id
GROUP BY t.service_template_model_stm_id
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
     * Get a chain of the related objects
     *
     * @param int[] $pollerIds
     * @param int[] $ba
     * @return array
     */
    public function getServiceIdsByPoller(array $pollerIds, array $ba = null): array
    {
        // prevent SQL exception
        if (!$pollerIds) {
            return [];
        }

        $ids = implode(',', $pollerIds);
        $sql = <<<SQL
SELECT l.* FROM (
SELECT DISTINCT t.service_id AS `id`
FROM service AS t
INNER JOIN host_service_relation AS hsr ON hsr.service_service_id = t.service_id
INNER JOIN ns_host_relation AS hr ON hr.host_host_id = hsr.host_host_id
WHERE hr.nagios_server_id IN ({$ids})
SQL;

        // Extract BA services
        if ($ba) {
            foreach ($ba as $key => $val) {
                $ba[$key] = "'ba_{$val}'";
            }

            $ba = implode(',', $ba);
            $sql .= <<<SQL
UNION

SELECT t2.service_id AS `id` 
FROM service AS t2 
WHERE t2.service_description IN({$ba})
SQL;
        }
        
        $sql .=<<<SQL
) AS l GROUP BY l.id
SQL;


        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[] = $row['id'];
        }

        return $result;
    }

    /**
     * Get a chain of the related objects
     *
     * @param int[] $serviceIDs
     * @return array
     */
    public function getChainByServiceIds(array $serviceIDs): array
    {
        // prevent SQL exception
        if (!$serviceIDs) {
            return [];
        }

        $ids = implode(',', $serviceIDs);
        $sql = <<<SQL
SELECT DISTINCT service_template_model_stm_id AS `id`
FROM service
WHERE service_id IN ({$ids})
GROUP BY service_template_model_stm_id
SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[$row['id']] = $row['id'];
            $this->getChainByParant($row['id'], $result);
        }

        return $result;
    }
}
