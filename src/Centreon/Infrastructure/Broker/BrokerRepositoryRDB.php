<?php

namespace Centreon\Infrastructure\Broker;

use Centreon\Domain\Broker\Broker;
use Centreon\Domain\Broker\BrokerConfiguration;
use Centreon\Infrastructure\DatabaseConnection;
use Centreon\Domain\Broker\Interfaces\BrokerRepositoryInterface;

class BrokerRepositoryRDB implements BrokerRepositoryInterface
{
    /**
     * @var DatabaseConnection
     */
    private $db;

    /**
     * BrokerRepositoryRDB constructor.
     * @param DatabaseConnection $db
     */
    public function __construct(DatabaseConnection $db)
    {
        $this->db = $db;
    }

    /**
     * Get Broker Configurations based on the configuration Key
     *
     * @param integer $monitoringServerId
     * @param string $configKey
     * @return array
     */
    public function findByMonitoringServerAndParameterName(int $monitoringServerId, string $configKey): array
    {
        $statement = $this->db->prepare("
            SELECT config_value, cfgbi.config_id AS id
                FROM cfg_centreonbroker_info cfgbi
                INNER JOIN cfg_centreonbroker AS cfgb
                    ON cfgbi.config_id = cfgb.config_id
                INNER JOIN nagios_server AS ns
                    ON cfgb.ns_nagios_server = ns.id
                    AND ns.id = :monitoringServerId
                WHERE config_key = :configKey
        ");
        $statement->bindValue(':monitoringServerId', $monitoringServerId, \PDO::PARAM_INT);
        $statement->bindValue(':configKey', $configKey, \PDO::PARAM_STR);
        $statement->execute();

        $brokerConfigurations = [];
        while (($result = $statement->fetch(\PDO::FETCH_ASSOC)) !== false) {
            $brokerConfigurations[] = (new BrokerConfiguration())
                ->setId($result['id'])
                ->setConfigurationKey($configKey)
                ->setConfigurationValue($result['config_value']);
        }

        return $brokerConfigurations;
    }
}
