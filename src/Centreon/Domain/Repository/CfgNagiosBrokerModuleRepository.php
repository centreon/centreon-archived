<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class CfgNagiosBrokerModuleRepository extends ServiceEntityRepository
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
FROM cfg_nagios_broker_module AS t
INNER JOIN cfg_nagios AS cn ON cn.nagios_id = t.cfg_nagios_id
WHERE cn.nagios_server_id IN ({$ids})
GROUP BY t.bk_mod_id
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
