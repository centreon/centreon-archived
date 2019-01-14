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


    public function __construct(Container $di)
    {
        $this->dbAdapter = $di['centreon.db-manager']->getAdapter('configuration_db');
    }

    public function hasPollersForUpdating(): bool
    {
        return !empty($this->pollers);
    }

    /**
     * @return PollerServer[]
     */
    public function getLinkedPollersSelectedForUpdate(): array
    {
        return $this->pollers;
    }

    /**
     * Set linked pollers regarding wizard type (poller/remote server)
     */
    public function collectDataFromRequest(): void
    {
        $isRemoteServerWizard = (new ServerWizardIdentity)->requestConfigurationIsRemote();

        if ($isRemoteServerWizard) { // configure remote server
            // pollers linked to the remote server
            $linkedPollers = isset($_POST['linked_pollers']) ? (array) $_POST['linked_pollers'] : [];
        } else { // configure poller
            // if the poller is linked to a remote server
            $linkedPollers = isset($_POST['linked_remote']) ? [$_POST['linked_remote']] : [];
        }

        $this->pollers = $this->getPollersToLink($linkedPollers); // set and instantiate linked pollers
    }

    /**
     * Get pollers to link a set of poller information
     *
     * @param array $pollers the pollers to get list of poller objects
     * @return PollerServer[] the pollers to link
     */
    private function getPollersToLink(array $pollers): array
    {
        if (empty($pollers)) {
            return [];
        }

        $pollerIDs = [];

        if (is_array(reset($pollers))) {
            foreach ($pollers as $poller) {
                if (isset($poller['value'])) {
                    $pollerIDs[] = $poller['value'];
                }
            }
        } else {
            $pollerIDs = $pollers;
        }

        $idBindString = str_repeat('?,', count($pollerIDs));
        $idBindString = rtrim($idBindString, ',');
        $queryPollers = "SELECT id, name, ns_ip_address as ip FROM nagios_server WHERE id IN ({$idBindString})";

        $this->dbAdapter->query($queryPollers, $pollerIDs);
        $results = $this->dbAdapter->results();
        $data = [];

        foreach ($results as $result) {
            $poller = new PollerServer;
            $poller->setId($result->id);
            $poller->setName($result->name);
            $poller->setIp($result->ip);

            $data[] = $poller;
        }

        return $data;
    }

    /**
     * Get poller information from poller id
     *
     * @param int $pollerId the poller id to get
     * @return null|PollerServer
     */
    public function getPollerFromId(int $pollerId): ?PollerServer
    {
        $query = 'SELECT id, name, ns_ip_address as ip FROM nagios_server WHERE id = ?';

        $this->dbAdapter->query($query, [$pollerId]);
        $results = $this->dbAdapter->results();

        if (count($results)) {
            $remoteData = reset($results);
            $remoteServer = new PollerServer;
            $remoteServer->setId($remoteData->id);
            $remoteServer->setName($remoteData->name);
            $remoteServer->setIp($remoteData->ip);

            return $remoteServer;
        }

        return null;
    }
}
