<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use Centreon\Domain\Entity\CfgResource;
use PDO;

class CfgResourceRepository extends ServiceEntityRepository
{

    /**
     * Export options
     * 
     * @return \Centreon\Domain\Entity\CfgResource[]
     */
    public function export(): array
    {
        $sql = <<<SQL
SELECT cr.resource_id AS `resourceId`,
    cr.resource_name AS `resourceName`,
    cr.resource_line AS `resourceLine`,
    cr.resource_comment AS `resourceComment`,
    cr.resource_activate AS `resourceActivate`
FROM cfg_resource as cr
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, CfgResource::class);

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[] = $row;
        }

        return $result;
    }
}
