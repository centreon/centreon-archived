<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class NagiosServerRepository extends ServiceEntityRepository
{

    /**
     * Export poller's Nagios data
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

        $sql = "SELECT * FROM nagios_server WHERE id IN ({$ids})";

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
TRUNCATE TABLE `nagios_server`;
TRUNCATE TABLE `cfg_nagios`;
TRUNCATE TABLE `cfg_nagios_broker_module`
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }
}
