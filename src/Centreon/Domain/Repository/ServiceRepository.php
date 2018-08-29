<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use PDO;

class ServiceRepository extends ServiceEntityRepository
{

    /**
     * Export
     * 
     * @todo restriction by poller
     * 
     * @param int $pollerId
     * @param array $templateChainList
     * @return array
     */
    public function export(int $pollerId, array $templateChainList = null): array
    {
        $sql = <<<SQL
SELECT l.* FROM(
SELECT
    t.*
FROM service AS t
INNER JOIN host_service_relation AS hsr ON hsr.service_service_id = t.service_id
LEFT JOIN hostgroup AS hg ON hg.hg_id = hsr.hostgroup_hg_id
LEFT JOIN hostgroup_relation AS hgr ON hgr.hostgroup_hg_id = hg.hg_id
INNER JOIN ns_host_relation AS hr ON hr.host_host_id = hsr.host_host_id OR hr.host_host_id = hgr.host_host_id
WHERE hr.nagios_server_id = :id
GROUP BY t.service_id
SQL;

        if ($templateChainList) {
            $list = join(',', $templateChainList);
            $sql .= <<<SQL

UNION
                
SELECT
    tt.*
FROM service AS tt
WHERE tt.service_id IN ({$list})
GROUP BY tt.service_id
SQL;
        }

        $sql .= <<<SQL
) AS l
GROUP BY l.service_id
SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $pollerId, PDO::PARAM_INT);
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

    public function getChainByPoller(int $pollerId): array
    {
        $sql = <<<SQL
SELECT
    t.service_template_model_stm_id AS `id`
FROM service AS t
INNER JOIN host_service_relation AS hsr ON hsr.service_service_id = t.service_id
LEFT JOIN hostgroup AS hg ON hg.hg_id = hsr.hostgroup_hg_id
LEFT JOIN hostgroup_relation AS hgr ON hgr.hostgroup_hg_id = hg.hg_id
INNER JOIN ns_host_relation AS hr ON hr.host_host_id = hsr.host_host_id OR hr.host_host_id = hgr.host_host_id
WHERE t.service_template_model_stm_id IS NOT NULL AND hr.nagios_server_id = :id
GROUP BY t.service_template_model_stm_id
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $pollerId, PDO::PARAM_INT);
        $stmt->execute();

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[$row['id']] = $row['id'];
            $this->getChainByParant($row['id'], $result);
        }

        return $result;
    }

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
}
