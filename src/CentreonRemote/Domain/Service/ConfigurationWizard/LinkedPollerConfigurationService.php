<?php

namespace CentreonRemote\Domain\Service\ConfigurationWizard;

require_once _CENTREON_PATH_ . 'www/class/centreonContactgroup.class.php';
require_once _CENTREON_PATH_ . 'www/class/config-generate/generate.class.php';
require_once _CENTREON_PATH_ . 'www/class/centreonBroker.class.php';
require_once _CENTREON_PATH_ . 'www/class/centreonConfigCentreonBroker.php';
require_once _CENTREON_PATH_ . 'www/include/configuration/configGenerate/DB-Func.php';

use Centreon\Infrastructure\CentreonLegacyDB\CentreonDBAdapter;
use Centreon\Domain\Entity\Task;
use CentreonRemote\Domain\Value\PollerServer;
use CentreonRemote\Infrastructure\Service\PollerInteractionService;
use Centreon\Domain\Repository\Interfaces\CfgCentreonBrokerInterface;
use Centreon\Domain\Service\BrokerConfigurationService;
use CentreonRemote\Domain\Service\TaskService;

use CentreonRemote\Domain\Resources\RemoteConfig\InputFlowOnePeerRetention;

class LinkedPollerConfigurationService
{
    /** @var \CentreonDB */
    private $db;

    protected $onePeerRetention = false;

    /**
     * @var CfgCentreonBrokerInterface
     */
    private $brokerRepository;

    /**
     * @var BrokerConfigurationService
     */
    private $brokerConfigurationService;

    /**
     * @var TaskService
     */
    private $taskService;

    /**
     * @var PollerInteractionService
     */
    private $pollerInteractionService;

    public function __construct(CentreonDBAdapter $dbAdapter)
    {
        $this->db = $dbAdapter->getCentreonDBInstance();
    }

    /**
     * Set broker repository to manage general broker configuration
     *
     * @param CfgCentreonBrokerInterface $cfgCentreonBroker the centreon broker configuration repository
     */
    public function setBrokerRepository(CfgCentreonBrokerInterface $cfgCentreonBroker)
    {
        $this->brokerRepository = $cfgCentreonBroker;
    }

    /**
     * Set broker configuration service to broker info configuration
     *
     * @param BrokerConfigurationService $brokerConfigurationService the service to manage broker confiration
     */
    public function setBrokerConfigurationService(BrokerConfigurationService $brokerConfigurationService)
    {
        $this->brokerConfigurationService = $brokerConfigurationService;
    }

    /**
     * Set poller interaction service
     *
     * @param PollerInteractionService $pollerInteractionService the poller interaction service
     */
    public function setPollerInteractionService(pollerInteractionService $pollerInteractionService)
    {
        $this->pollerInteractionService = $pollerInteractionService;
    }

    /**
     * Set task service to add export task
     *
     * @param TaskService $taskService the task service
     */
    public function setTaskService(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Set one peer retention mode
     *
     * @param bool $onePeerRetention if one peer retention mode is enabled
     */
    public function setOnePeerRetention(bool $onePeerRetention): void
    {
        $this->onePeerRetention = $onePeerRetention;
    }

    /**
     * Link a set of pollers to a parent poller by creating broker input/output
     *
     * @param PollerServer[] $pollers
     * @param PollerServer   $remote
     */
    public function linkPollersToParentPoller(array $pollers, PollerServer $remote)
    {
        $pollerIds = array_map(function ($poller) {
            return $poller->getId();
        }, $pollers);

        // Before linking the pollers to the new remote, we have to tell the old remote they are no longer linked to it
        $this->triggerExportForOldRemotes($pollerIds);

        foreach ($pollers as $poller) {
            // If one peer retention is enabled, add input on remote server to get data from poller
            if ($this->onePeerRetention) {
                $this->setBrokerInputOfRemoteServer($remote->getId(), $poller);
            } else { // If one peer retention is disabled, we need to set the host output of the poller
                $this->setBrokerOutputOfPoller($poller->getId(), $remote);
            }

            $this->setPollerRelationToRemote($poller->getId(), $remote);
        }

        // Generate configuration for pollers and restart them
        $this->pollerInteractionService->generateAndExport($pollerIds);
    }

    /**
     * Add broker input configuration on remote server to get data from poller
     *
     * @param int $pollerId
     * @param PollerServer $remote
     */
    private function setBrokerInputOfRemoteServer($remoteId, PollerServer $poller): void
    {
        // get broker config id of linked remote server (cbd broker)
        $remoteBrokerConfigId = $this->brokerRepository->findBrokerConfigIdByPollerId($remoteId);

        // get template function to generate input flow in remote server broker configuration
        $brokerInfosEntities = InputFlowOnePeerRetention::getConfiguration($poller->getName(), $poller->getIp());
        $this->brokerConfigurationService->addFlow($remoteBrokerConfigId, 'input', $brokerInfosEntities);
    }

    /**
     * Update host field of broker output on poller to link it the the remote server
     *
     * @param int $pollerId
     * @param PollerServer $remote
     */
    private function setBrokerOutputOfPoller($pollerId, PollerServer $remote): void
    {
        // find broker config id of poller module
        $configQuery = "SELECT `config_id` "
            . "FROM `cfg_centreonbroker` "
            . "WHERE `ns_nagios_server` = :id "
            . "AND `daemon` = 0";
        $statement = $this->db->prepare($configQuery);
        $statement->bindParam(':id', $pollerId, \PDO::PARAM_INT);
        $statement->execute();
        $configId = $statement->fetchColumn();

        // update host field of poller module output to link it the remote server
        $updateQuery = "UPDATE `cfg_centreonbroker_info` "
            . "SET `config_value` = :config_value "
            . "WHERE `config_id` = :config_id "
            . "AND `config_key` = 'host'";
        $statement = $this->db->prepare($updateQuery);
        $statement->bindValue(':config_value', $remote->getIp(), \PDO::PARAM_STR);
        $statement->bindValue(':config_id', $configId, \PDO::PARAM_INT);
        $statement->execute();
    }

    /**
     * Link poller with remote server in database
     *
     * @param int $pollerId
     * @param PollerServer $remote
     */
    private function setPollerRelationToRemote($pollerId, PollerServer $remote): void
    {
        $query = "UPDATE `nagios_server` "
            . "SET `remote_id` = :remote_id "
            . "WHERE `id` = :id";
        $statement = $this->db->prepare($query);
        $statement->bindValue(':remote_id', $remote->getId(), \PDO::PARAM_INT);
        $statement->bindValue(':id', $pollerId, \PDO::PARAM_INT);
        $statement->execute();
    }

    /**
     * Export to existing remote servers
     *
     * @param int[] $pollerIDs the poller ids to export
     * @return void
     */
    private function triggerExportForOldRemotes(array $pollerIDs): void
    {
        // Get from the database only the pollers that are linked to a remote
        $idBindString = str_repeat('?,', count($pollerIDs));
        $idBindString = rtrim($idBindString, ',');
        $queryPollers = "SELECT id, remote_id FROM nagios_server WHERE id IN({$idBindString}) AND remote_id IS NOT NULL";
        $remotesStatement = $this->db->query($queryPollers, $pollerIDs);
        $pollersWithRemote = $remotesStatement->fetchAll(\PDO::FETCH_ASSOC);
        $alreadyExportedRemotes = [];

        // For each remote get the currently linked pollers, exclude the ones selected and trigger export
        foreach ($pollersWithRemote as $poller) {
            $remoteID = $poller['remote_id'];

            if (in_array($remoteID, $alreadyExportedRemotes)) {
                continue;
            }

            $alreadyExportedRemotes[] = $remoteID;

            // Get all linked pollers of the remote
            $queryPollersOfRemote = "SELECT id FROM nagios_server WHERE remote_id = {$remoteID}";
            $linkedStatement = $this->db->query($queryPollersOfRemote);
            $linkedResults = $linkedStatement->fetchAll(\PDO::FETCH_ASSOC);
            $linkedPollersOfRemote = array_column($linkedResults, 'id');

            // Get IP of remote
            $queryRemoteData = "SELECT ns.ns_ip_address as ip, rs.centreon_path, rs.http_method, rs.http_port, " .
                " rs.no_check_certificate FROM nagios_server as ns " .
                " JOIN remote_servers as rs ON rs.ip = ns.ns_ip_address " .
                " WHERE ns.id = {$remoteID}";
            $remoteDataStatement = $this->db->query($queryRemoteData);
            $remoteDataResults = $remoteDataStatement->fetchAll(\PDO::FETCH_ASSOC);

            // Exclude the selected pollers which are going to another remote
            $pollerIDsToExport = array_diff($linkedPollersOfRemote, $pollerIDs);

            $exportParams = [
                'server'               => $remoteID,
                'pollers'              => $pollerIDsToExport,
                'remote_ip'            => $remoteDataResults[0]['ip'],
                'centreon_path'        => $remoteDataResults[0]['centreon_path'],
                'http_method'          => $remoteDataResults[0]['http_method'],
                'http_port'            => $remoteDataResults[0]['http_port'],
                'no_check_certificate' => $remoteDataResults[0]['no_check_certificate'],
            ];
            $this->taskService->addTask(Task::TYPE_EXPORT, ['params' => $exportParams]);
        }
    }
}
