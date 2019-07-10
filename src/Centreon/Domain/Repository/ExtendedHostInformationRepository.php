<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class ExtendedHostInformationRepository extends ServiceEntityRepository
{

    /**
     * Export host's macros
     *
     * @param int[] $hostList
     * @param array $templateChainList
     * @return array
     */
    public function export(array $hostList, array $templateChainList = null): array
    {
        // prevent SQL exception
        if (!$hostList) {
            return [];
        }

        if ($templateChainList) {
            $hostList = array_merge($hostList, $templateChainList);
        }

        $ids = join(',', $hostList);

        $sql = <<<SQL
SELECT
    t.*
FROM extended_host_information AS t
WHERE t.host_host_id IN ({$ids})
GROUP BY t.ehi_id
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
