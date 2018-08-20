<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use PDO;

class ExtendedHostInformationRepository extends ServiceEntityRepository
{

    /**
     * Export host's macros
     * 
     * @param int $pollerId
     * @return array
     */
    public function export(int $pollerId): array
    {
        $sql = <<<SQL
SELECT
    t.*
FROM extended_host_information AS t
INNER JOIN host AS h ON h.host_id = t.host_host_id
INNER JOIN ns_host_relation AS hr ON hr.host_host_id = h.host_id
WHERE hr.nagios_server_id = :id
GROUP BY t.host_macro_id
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
