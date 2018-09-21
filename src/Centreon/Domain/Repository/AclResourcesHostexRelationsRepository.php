<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use Centreon\Domain\Repository\Interfaces\AclResourceRefreshInterface;

class AclResourcesHostexRelationsRepository extends ServiceEntityRepository implements AclResourceRefreshInterface
{

    /**
     * Refresh
     */
    public function refresh(): void
    {
        $sql = <<<SQL
DELETE FROM acl_resources_hostex_relations
    WHERE host_host_id NOT IN (SELECT t2.host_id FROM host AS t2)
SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }
}
