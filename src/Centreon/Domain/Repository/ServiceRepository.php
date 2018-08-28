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
     * @return array
     */
    public function export(int $pollerId): array
    {
        $sql = <<<SQL
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
}
