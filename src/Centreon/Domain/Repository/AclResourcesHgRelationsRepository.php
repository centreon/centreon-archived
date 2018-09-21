<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use Centreon\Domain\Repository\Interfaces\AclResourceRefreshInterface;

class AclResourcesHgRelationsRepository extends ServiceEntityRepository implements AclResourceRefreshInterface
{

    /**
     * Refresh
     */
    public function refresh(): void
    {
        $sql = <<<SQL
DELETE FROM acl_resources_hg_relations
    WHERE hg_hg_id NOT IN (SELECT t2.hg_id FROM hostgroup AS t2)
SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }
}
