<?php

namespace CentreonRemote\Domain\Service\ConfigurationWizard;

use Centreon\Infrastructure\CentreonLegacyDB\CentreonDBAdapter;
use CentreonRemote\Domain\Value\PollerServer;
use CentreonRemote\Domain\Value\ServerWizardIdentity;
use Pimple\Container;

class PollerConfigurationRequestBridge
{

    /** @var CentreonDBAdapter */
    private $dbAdapter;

    /** @var PollerServer[] */
    private $pollers = [];

    /** @var PollerServer */
    private $remoteServer;

    private $serverID;


    public function __construct(Container $di)
    {
        $this->dbAdapter = $di['centreon.db-manager']->getAdapter('configuration_db');
    }


    public function setServerID($serverID)
    {
        $this->serverID = $serverID;
    }

    public function hasPollersForUpdating(): bool
    {
        return !empty($this->pollers) && $this->remoteServer;
    }

    /**
     * @return PollerServer[]
     */
    public function getLinkedPollersSelectedForUpdate(): array
    {
        return $this->pollers;
    }

    public function getRemoteServerForConfiguration(): ?PollerServer
    {
        return $this->remoteServer;
    }

    public function collectDataFromRequest()
    {
        $isRemoteConnection = ServerWizardIdentity::requestConfigurationIsRemote();

        if ($isRemoteConnection) {
            $this->collectPollersToLink();

            //todo: use the $serverID to get data for the remote from nagios_server
            $this->remoteServer = null; // PollerServer
        } else {
            $this->collectRemoteForConfiguration();
            //todo: use the $serverID to get data for the poller from nagios_server
            $this->pollers = [[]]; // PollerServer
        }
    }

    private function collectPollersToLink()
    {
        $idBindString = '';
        $pollerIDs = $_POST['linked_pollers'] ?? '';
        $pollerIDs = (array) $pollerIDs;

        if (empty($pollerIDs)) {
            $this->pollers = [];
            return;
        }

        foreach ($pollerIDs as $key => $id) {
            $idBindString .= "?,";
        }

        $idBindString = rtrim($idBindString, ',');
        $queryPollers = "SELECT id, ns_ip_address as ip FROM nagios_server WHERE id IN({$idBindString})";

        try {
            $this->dbAdapter->query($queryPollers, $pollerIDs);
            $results = $this->dbAdapter->results();

            foreach ($results as $result) {
                $poller = new PollerServer;
                $poller->setId($result->id);
                $poller->setIp($result->ip);

                $this->pollers[] = $poller;
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }

    private function collectRemoteForConfiguration()
    {
        $remoteID = $_POST['linked_remote'] ?? '';
        $queryPollers = 'SELECT id, ns_ip_address as ip FROM nagios_server WHERE id=?';

        if (empty($remoteID)) {
            $this->remoteServer = null;
            return;
        }

        try {
            $this->dbAdapter->query($queryPollers, [$remoteID]);
            $results = $this->dbAdapter->results();

            if (count($results)) {
                $remoteData = reset($results);
                $this->remoteServer = new PollerServer;
                $this->remoteServer->setId($remoteData->id);
                $this->remoteServer->setIp($remoteData->ip);
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }
}
