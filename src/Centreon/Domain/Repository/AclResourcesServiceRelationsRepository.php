<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use Centreon\Domain\Repository\Interfaces\AclResourceRefreshInterface;

class AclResourcesServiceRelationsRepository extends ServiceEntityRepository implements AclResourceRefreshInterface
{
    /**
     * Refresh
     */
    public function refresh(): void
    {
        $sql = "DELETE FROM acl_resources_service_relations "
            . "WHERE service_service_id NOT IN (SELECT t2.service_id FROM service AS t2)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }
}
