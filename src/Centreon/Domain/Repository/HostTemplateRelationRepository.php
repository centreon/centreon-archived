<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use PDO;

class HostTemplateRelationRepository extends ServiceEntityRepository
{

    /**
     * Export host's templates relation
     * 
     * @param int[] $pollerIds
     * @param array $templateChainList
     * @return array
     */
    public function export(array $pollerIds, array $templateChainList = null): array
    {
        // prevent SQL exception
        if (!$pollerIds) {
            return [];
        }

        $ids = join(',', $pollerIds);

        $sql = <<<SQL
SELECT l.* FROM(
SELECT
    t.*
FROM host_template_relation AS t
INNER JOIN ns_host_relation AS hr ON hr.host_host_id = t.host_host_id
WHERE hr.nagios_server_id IN ({$ids})
GROUP BY t.host_host_id, t.host_tpl_id
SQL;
        if ($templateChainList) {
            $list = join(',', $templateChainList);
        $sql .= <<<SQL

UNION

SELECT
    tt.*
FROM host_template_relation AS tt
WHERE tt.host_host_id IN ({$list})
GROUP BY tt.host_host_id, tt.host_tpl_id
SQL;
        }
        
        $sql .= <<<SQL
) AS l
GROUP BY l.host_host_id, l.host_tpl_id
SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[] = $row;
        }

        return $result;
    }

    /**
     * Get a chain of the related objects
     * 
     * @param int[] $pollerIds
     * @return array
     */
    public function getChainByPoller(array $pollerIds): array
    {
        // prevent SQL exception
        if (!$pollerIds) {
            return [];
        }

        $ids = join(',', $pollerIds);
        $sql = <<<SQL
SELECT
    t.host_tpl_id AS `id`
FROM host_template_relation AS t
INNER JOIN ns_host_relation AS hr ON hr.host_host_id = t.host_host_id
WHERE hr.nagios_server_id IN ({$ids})
GROUP BY t.host_tpl_id
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $result = [];

        while ($row = $stmt->fetch()) {
            $result[$row['id']] = $row['id'];
            $this->getChainByParant($row['id'], $result);
        }

        return $result;
    }

    public function getChainByParant($id, &$result)
    {
        $sql = <<<SQL
SELECT
    t.host_tpl_id AS `id`
FROM host_template_relation AS t
WHERE t.host_host_id = :id
GROUP BY t.host_tpl_id
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        while ($row = $stmt->fetch()) {
            $result[$row['id']] = $row['id'];
            
            $this->getChainByParant($row['id'], $result);
        }

        return $result;
    }
}
