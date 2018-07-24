<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use Centreon\Domain\Entity\CfgResourceInstanceRelations;
use PDO;

class CfgResourceInstanceRelationsRepository extends ServiceEntityRepository
{

    /**
     * Export options
     * 
     * @return \Centreon\Domain\Entity\CfgResourceInstanceRelations[]
     */
    public function export(): array
    {
        $sql = <<<SQL
SELECT
    crir.resource_id AS `resourceId`,
    crir.instance_id AS `instanceId`
FROM cfg_resource_instance_relations AS `crir`
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, CfgResourceInstanceRelations::class);

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[] = $row;
        }

        return $result;
    }
}
