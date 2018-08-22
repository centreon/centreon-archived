<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use PDO;

class PollerCommandRelationsRepository extends ServiceEntityRepository
{

    /**
     * Export
     * 
     * @param int $pollerId
     * @return array
     */
    public function export(int $pollerId): array
    {
        $sql = <<<SQL
SELECT
    t.*
FROM poller_command_relations AS t
WHERE t.poller_id = :id
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
TRUNCATE TABLE `poller_command_relations`
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }
}
