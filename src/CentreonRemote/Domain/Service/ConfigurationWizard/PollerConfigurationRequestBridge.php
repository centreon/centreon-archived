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
            $pollerIDs = $_POST['linked_pollers'] ?? ''; // Poller ids are coming form the request
            $pollerIDs = (array) $pollerIDs;
            $this->pollers = $this->getPollersToLink($pollerIDs);
            $this->remoteServer = $this->getRemoteForConfiguration($this->serverID); // The server id is of the new remote
        } else {
            $remoteID = $_POST['linked_remote'] ?? ''; // Remote id is coming from the request
            $this->remoteServer = $this->getRemoteForConfiguration($remoteID);
            $this->pollers = $this->getPollersToLink([$this->serverID]); // The server id is of the new poller
        }
    }

    private function getPollersToLink(array $pollerIDs)
    {
        $idBindString = '';

        if (empty($pollerIDs)) {
            return [];
        }

        foreach ($pollerIDs as $key => $id) {
            $idBindString .= "?,";
        }

        $idBindString = rtrim($idBindString, ',');
        $queryPollers = "SELECT id, ns_ip_address as ip FROM nagios_server WHERE id IN({$idBindString})";

        try {
            $this->dbAdapter->query($queryPollers, $pollerIDs);
            $results = $this->dbAdapter->results();
            $data = [];

            foreach ($results as $result) {
                $poller = new PollerServer;
                $poller->setId($result->id);
                $poller->setIp($result->ip);

                $data[] = $poller;
            }

            return $data;
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

        return [];
    }

    private function getRemoteForConfiguration($remoteID)
    {
        $queryPollers = 'SELECT id, ns_ip_address as ip FROM nagios_server WHERE id=?';

        if (empty($remoteID)) {
            return null;
        }

        try {
            $this->dbAdapter->query($queryPollers, [$remoteID]);
            $results = $this->dbAdapter->results();

            if (count($results)) {
                $remoteData = reset($results);
                $remoteServer = new PollerServer;
                $remoteServer->setId($remoteData->id);
                $remoteServer->setIp($remoteData->ip);

                return $remoteServer;
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

        return null;
    }
}
