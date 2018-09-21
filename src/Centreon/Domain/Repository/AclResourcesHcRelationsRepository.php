<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use Centreon\Domain\Repository\Interfaces\AclResourceRefreshInterface;

class AclResourcesHcRelationsRepository extends ServiceEntityRepository implements AclResourceRefreshInterface
{

    /**
     * Refresh
     */
    public function refresh(): void
    {
        $sql = <<<SQL
DELETE FROM acl_resources_hc_relations
    WHERE hc_id NOT IN (SELECT t2.hc_id FROM hostcategories AS t2)
SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }
}
