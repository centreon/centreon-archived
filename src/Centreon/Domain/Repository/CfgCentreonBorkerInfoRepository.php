<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use PDO;

class CfgCentreonBorkerInfoRepository extends ServiceEntityRepository
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
SELECT t.*
FROM cfg_centreonbroker_info AS t
INNER JOIN cfg_centreonbroker AS cci ON cci.config_id = t.config_id
WHERE cci.ns_nagios_server = :id
GROUP BY t.config_id
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
}
