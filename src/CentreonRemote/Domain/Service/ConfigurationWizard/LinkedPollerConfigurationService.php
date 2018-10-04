<?php

namespace CentreonRemote\Domain\Service\ConfigurationWizard;

require_once _CENTREON_PATH_ . 'www/class/centreonContactgroup.class.php';
require_once _CENTREON_PATH_ . 'www/class/config-generate/generate.class.php';
require_once _CENTREON_PATH_ . 'www/class/centreonBroker.class.php';
require_once _CENTREON_PATH_ . 'www/class/centreonConfigCentreonBroker.php';
require_once _CENTREON_PATH_ . 'www/include/configuration/configGenerate/DB-Func.php';

use Centreon\Domain\Entity\Task;
use CentreonRemote\Domain\Value\PollerServer;
use CentreonRemote\Infrastructure\Service\PollerInteractionService;
use Pimple\Container;

class LinkedPollerConfigurationService
{

    /** @var Container */
    private $di;

    /** @var \CentreonDB */
    private $db;

    /** @var PollerInteractionService */
    private $pollerInteraction;

    protected $isOpenBrokerFlow = false;


    public function __construct(Container $di)
    {
        $this->di = $di;
        $this->db = $di['centreon.db-manager']->getAdapter('configuration_db')->getCentreonDBInstance();
        $this->pollerInteraction = new PollerInteractionService($di);
    }

    public function setOpenBrokerFlow($openBrokerFlow)
    {
        $this->isOpenBrokerFlow = $openBrokerFlow;
    }

    /**
     * @param PollerServer[] $pollers
     * @param PollerServer   $remote
     */
    public function setPollersConfigurationWithServer(array $pollers, PollerServer $remote)
    {
        $pollerIDs = array_map(function ($poller) {
            return $poller->getId();
        }, $pollers);

        // Before linking the pollers to the new remote, we have to tell the old remote they are no longer linked to it
        $this->triggerExportForOldRemotes($pollerIDs);

        foreach ($pollers as $poller) {
            $pollerID = $poller->getId();

            // If we do not have an open broker flow we need to set the host output of the poller
            if (!$this->isOpenBrokerFlow) {
                $this->setBrokerOutputOfPoller($pollerID, $remote);
            }

            $this->setPollerRelationToRemote($pollerID, $remote);
        }

        // Generate configuration for pollers and restart them
        $this->pollerInteraction->generateAndExport($pollerIDs);
    }

    private function setBrokerOutputOfPoller($pollerID, PollerServer $remote)
    {
        $configQuery = "SELECT `config_id` FROM `cfg_centreonbroker` WHERE `ns_nagios_server` = :id 
                        AND `config_filename` LIKE '%-module.xml'";
        $statement = $this->db->prepare($configQuery);
        $statement->execute([':id' => $pollerID]);
        $configID = $statement->fetchColumn();

        $updateQuery = "UPDATE `cfg_centreonbroker_info` SET `config_value` = '{$remote->getIp()}' 
                        WHERE `config_id` = {$configID} AND `config_key` = 'host'";
        $this->db->query($updateQuery);
    }

    private function setPollerRelationToRemote($pollerID, PollerServer $remote)
    {
        $query = "UPDATE `nagios_server` SET `remote_id` = '{$remote->getId()}' 
                        WHERE `id` = :id";
        $statement = $this->db->prepare($query);
        $statement->execute([':id' => $pollerID]);
    }

    private function triggerExportForOldRemotes($pollerIDs)
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
            $queryRemoteData = "SELECT ns.ns_ip_address as ip, rs.centreon_path FROM nagios_server as ns " .
                " JOIN remote_servers as rs ON rs.ip = ns.ns_ip_address " .
                " WHERE ns.id = {$remoteID}";
            $remoteDataStatement = $this->db->query($queryRemoteData);
            $remoteDataResults = $remoteDataStatement->fetchAll(\PDO::FETCH_ASSOC);

            // Exclude the selected pollers which are going to another remote
            $pollerIDsToExport = array_diff($linkedPollersOfRemote, $pollerIDs);

            $exportParams = [
                'server'        => $remoteID,
                'pollers'       => $pollerIDsToExport,
                'remote_ip'     => $remoteDataResults[0]['ip'],
                'centreon_path' => $remoteDataResults[0]['centreon_path'],
            ];
            $this->di['centreon.taskservice']->addTask(Task::TYPE_EXPORT, ['params' => $exportParams]);
        }
    }
}
