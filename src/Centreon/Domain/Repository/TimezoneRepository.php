<?php
namespace Centreon\Domain\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;
use PDO;

class TimezoneRepository extends ServiceEntityRepository
{
    /**
     * Get by ID
     *
     * @param int $id
     * @return array
     */
    public function get(int $id): ?array
    {
        $sql = <<<SQL
SELECT
    t.*
FROM timezone AS t
WHERE t.timezone_id = :id
LIMIT 0, 1
SQL;
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            return null;
        }
        
        $result = $stmt->fetch();

        return $result;
    }

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
SELECT
    tz.*,
    t.nagios_id AS `_nagios_id`
FROM cfg_nagios AS t
INNER JOIN timezone AS tz ON tz.timezone_id = t.use_timezone
WHERE t.nagios_id IN ({$ids})
GROUP BY tz.timezone_id
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
