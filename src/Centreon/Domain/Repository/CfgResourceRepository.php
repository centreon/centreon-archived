<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use Centreon\Domain\Entity\CfgResource;
use PDO;

class CfgResourceRepository extends ServiceEntityRepository
{

    /**
     * Export cfg resources
     * 
     * @param int $pollerId
     * @return array
     */
    public function export(int $pollerId): array
    {
        $sql = <<<SQL
SELECT
    t.*,
    crir.instance_id AS `_instance_id`
FROM cfg_resource AS t
INNER JOIN cfg_resource_instance_relations AS crir ON crir.resource_id = t.resource_id
WHERE crir.instance_id = :id
GROUP BY t.resource_id
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $pollerId, PDO::PARAM_INT);
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
TRUNCATE TABLE `cfg_resource`;
TRUNCATE TABLE `cfg_resource_instance_relations`
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }
}
