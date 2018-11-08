<?php
namespace Centreon\Domain\Repository\Interfaces;

use Centreon\Domain\Entity\CfgCentreonBrokerInfo;

interface CfgCentreonBrokerInfoInterface
{
    /**
     * Get new config group id by config id for a specific flow
     * once the config group is got from this method, it is possible to insert a new flow in the broker configuration
     *
     * @param int $configId the broker configuration id
     * @param string $flow the flow type : input, output, log...
     * @return int the new config group id
     */
    public function getNewConfigGroupId(int $configId, string $flow): int;

    /**
     * Insert broker configuration in database (table cfg_centreonbroker_info)
     *
     * @param CfgCentreonBrokerInfo $brokerInfoEntity the broker info entity
     */
    public function add(CfgCentreonBrokerInfo $cfgCentreonBrokerInfo): void;
}
