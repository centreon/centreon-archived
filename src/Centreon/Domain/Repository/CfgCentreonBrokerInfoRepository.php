<?php
namespace Centreon\Domain\Repository;

use Centreon\Domain\Repository\Interfaces\CfgCentreonBrokerInfoInterface;
use Centreon\Domain\Entity\CfgCentreonBrokerInfo;
use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

/**
 * Repository to manage centreon broker flows configuration (input, output...)
 */
class CfgCentreonBrokerInfoRepository extends ServiceEntityRepository implements CfgCentreonBrokerInfoInterface
{
    /**
     * Get new config group id by config id for a specific flow
     * once the config group is got from this method, it is possible to insert a new flow in the broker configuration
     *
     * @param int $configId the broker configuration id
     * @param string $flow the flow type : input, output, log...
     * @return int the new config group id
     */
    public function getNewConfigGroupId(int $configId, string $flow): int
    {
        // if there is no config group for a specific flow, first one id is 0
        $configGroupId = 0;

        $sql = 'SELECT MAX(config_group_id) as max_config_group_id '
            . 'FROM cfg_centreonbroker_info cbi '
            . 'WHERE config_id = :config_id '
            . 'AND config_group = :config_group';
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':config_id', $configId, \PDO::PARAM_INT);
        $stmt->bindParam(':config_group', $flow, \PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch();
        if (!is_null($row['max_config_group_id'])) {
            // to get the new new config group id, we need to increment the max exsting one
            $configGroupId = $row['max_config_group_id'] + 1;
        }

        return $configGroupId;
    }

    /**
     * Insert broker configuration in database (table cfg_centreonbroker_info)
     *
     * @param CfgCentreonBrokerInfo $brokerInfoEntity the broker info entity
     */
    public function add(CfgCentreonBrokerInfo $brokerInfoEntity): void
    {
        $sql = "INSERT INTO " . $brokerInfoEntity::TABLE . ' '
            . '(config_id, config_group, config_group_id, config_key, config_value) '
            . 'VALUES (:config_id, :config_group, :config_group_id, :config_key, :config_value)';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':config_id', $brokerInfoEntity->getConfigId(), \PDO::PARAM_INT);
        $stmt->bindValue(':config_group', $brokerInfoEntity->getConfigGroup(), \PDO::PARAM_STR);
        $stmt->bindValue(':config_group_id', $brokerInfoEntity->getConfigGroupId(), \PDO::PARAM_INT);
        $stmt->bindValue(':config_key', $brokerInfoEntity->getConfigKey(), \PDO::PARAM_STR);
        $stmt->bindValue(':config_value', $brokerInfoEntity->getConfigValue(), \PDO::PARAM_STR);
        $stmt->execute();
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
