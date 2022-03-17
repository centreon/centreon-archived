<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use Centreon\Domain\Repository\Interfaces\AclResourceRefreshInterface;

class AclResourcesMetaRelationsRepository extends ServiceEntityRepository implements AclResourceRefreshInterface
{
    /**
     * Refresh
     */
    public function refresh(): void
    {
        $sql = "DELETE FROM acl_resources_meta_relations "
            . "WHERE meta_id NOT IN (SELECT t2.meta_id FROM meta_service AS t2)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }
}
