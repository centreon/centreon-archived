<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use Centreon\Domain\Repository\Interfaces\AclResourceRefreshInterface;

class AclResourcesPollerRelationsRepository extends ServiceEntityRepository implements AclResourceRefreshInterface
{
    /**
     * Refresh
     */
    public function refresh(): void
    {
        $sql = "DELETE FROM acl_resources_poller_relations "
            . "WHERE poller_id NOT IN (SELECT t2.id FROM nagios_server AS t2)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }
}
