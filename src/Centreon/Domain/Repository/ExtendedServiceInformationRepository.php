<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use PDO;

class ExtendedServiceInformationRepository extends ServiceEntityRepository
{

    /**
     * Export host's macros
     * 
     * @todo restriction by poller
     * 
     * @param int $pollerId
     * @return array
     */
    public function export(int $pollerId): array
    {
        $sql = <<<SQL
SELECT
    t.*
FROM extended_service_information AS t
INNER JOIN service AS s ON s.service_id = t.service_service_id
INNER JOIN servicegroup_relation AS sgr ON sgr.service_service_id = t.service_id
INNER JOIN host AS h ON h.host_id = sgr.host_host_id
INNER JOIN ns_host_relation AS hr ON hr.host_host_id = h.host_id
WHERE hr.nagios_server_id = :id
GROUP BY t.service_id
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $pollerId, PDO::PARAM_INT);
        $stmt->execute();

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[] = $row;
        }

        return $result;
    }
}
