<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class CfgCentreonBorkerInfoRepository extends ServiceEntityRepository
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

        $ids = join(',', $pollerIds);

        $sql = <<<SQL
SELECT t.*
FROM cfg_centreonbroker_info AS t
INNER JOIN cfg_centreonbroker AS cci ON cci.config_id = t.config_id
WHERE cci.ns_nagios_server IN ({$ids})
SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[] = $row;
        }

        return $result;
    }
}
