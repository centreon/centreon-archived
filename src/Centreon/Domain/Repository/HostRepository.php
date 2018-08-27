<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use PDO;

class HostRepository extends ServiceEntityRepository
{

    /**
     * Export hosts
     * 
     * @param int $pollerId
     * @return array
     */
    public function export(int $pollerId): array
    {
        $sql = <<<SQL
SELECT
    t.*,
    hr.nagios_server_id AS `_nagios_id`
FROM host AS t
INNER JOIN ns_host_relation AS hr ON hr.host_host_id = t.host_id
WHERE hr.nagios_server_id = :id
GROUP BY t.host_id
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
TRUNCATE TABLE `ns_host_relation`;
TRUNCATE TABLE `hostgroup_relation`;
TRUNCATE TABLE `hostgroup`;
TRUNCATE TABLE `hostcategories_relation`;
TRUNCATE TABLE `hostcategories`;
TRUNCATE TABLE `host_hostparent_relation`;
TRUNCATE TABLE `on_demand_macro_host`;
TRUNCATE TABLE `hostgroup_hg_relation`;
TRUNCATE TABLE `extended_host_information`;
TRUNCATE TABLE `host`;
TRUNCATE TABLE `host_template_relation`;
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }
}
