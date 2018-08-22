<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use PDO;

class NagiosServerRepository extends ServiceEntityRepository
{

    /**
     * Export poller's Nagios data
     * 
     * @param int $pollerId
     * @return array
     */
    public function export(int $pollerId): array
    {
        $sql = 'SELECT * FROM nagios_server WHERE id = :id';
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
TRUNCATE TABLE `nagios_server`;
TRUNCATE TABLE `cfg_nagios`;
TRUNCATE TABLE `cfg_nagios_broker_module`
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
    }
}
