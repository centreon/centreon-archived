<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use PDO;

class CfgResourceInstanceRelationsRepository extends ServiceEntityRepository
{
    /**
     * Export
     *
     * @param int[] $pollerIds
     * @return array
     */
    public function export(array $pollerIds): array
    {
        // prevent SQL exception
        if (!$pollerIds) {
            return [];
        }

        $ids = implode(',', $pollerIds);

        $sql = "SELECT t.* "
            . "FROM cfg_resource_instance_relations AS t "
            . "WHERE t.instance_id IN ({$ids})"
        ;

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[] = $row;
        }

        return $result;
    }
}
