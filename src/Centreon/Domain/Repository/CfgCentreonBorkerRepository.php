<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class CfgCentreonBorkerRepository extends ServiceEntityRepository
{

    /**
     * Export poller's broker configurations
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
SELECT * FROM cfg_centreonbroker WHERE ns_nagios_server IN ({$ids})
SQL;

        $stmt = $this->db->prepare($sql);
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
TRUNCATE TABLE `cfg_centreonbroker`;
TRUNCATE TABLE `cfg_centreonbroker_info`
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }
}
