<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use Centreon\Domain\Repository\Interfaces\AclResourceRefreshInterface;

class AclResourcesScRelationsRepository extends ServiceEntityRepository implements AclResourceRefreshInterface
{
    /**
     * Refresh
     */
    public function refresh(): void
    {
        $sql = "DELETE FROM acl_resources_sc_relations "
            . "WHERE sc_id NOT IN (SELECT t2.sc_id FROM service_categories AS t2)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }
}
