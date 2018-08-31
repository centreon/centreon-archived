<?php

namespace CentreonRemote\Domain\Service\ConfigurationWizard;

use Centreon\Infrastructure\CentreonLegacyDB\CentreonDBAdapter;
use CentreonRemote\Domain\Value\ServerWizardIdentity;
use Pimple\Container;

class PollerConfigurationRequestBridge
{

    /** @var CentreonDBAdapter */
    private $dbAdapter;

    private $pollers = [];

    private $remoteServer = [];

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
        return !empty($this->pollers) && !empty($this->remoteServer);
    }

    public function getLinkedPollersSelectedForUpdate(): array
    {
        return $this->pollers;
    }

    public function getRemoteServerForConfiguration(): array
    {
        return $this->remoteServer;
    }

    public function collectDataFromRequest()
    {
        $isRemoteConnection = ServerWizardIdentity::requestConfigurationIsRemote();

        if ($isRemoteConnection) {
            $this->collectPollersToLink();

            //todo: use the $serverID to get data for the remote from nagios_server
            $this->remoteServer = [];
        } else {
            $this->collectRemoteForConfiguration();
            //todo: use the $serverID to get data for the poller from nagios_server
            $this->pollers = [[]];
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
            $this->pollers = $this->dbAdapter->results();
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }

    private function collectRemoteForConfiguration()
    {
        // IF CONNECTING POLLER
        // I can have (not required, can be empty) a $_POST remote server ip linked to this centreon

        // $serverID is the one of the new poller
        // - get ip of remote from $_POST

        $this->remoteServer = null;
    }
}
